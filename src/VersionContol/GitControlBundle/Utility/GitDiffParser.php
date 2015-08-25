<?php
// src/VersionContol/GitControlBundle/Utility/GitDiffParser.php
namespace VersionContol\GitControlBundle\Utility;

use VersionContol\GitControlBundle\Entity\GitDiff;
use VersionContol\GitControlBundle\Entity\GitDiffLine;

/**
 * Description of GitDiffParser
 *
 * @author fr_user
 */
class GitDiffParser {
    
    protected $lines;
    
    protected $lineCount;


    /**
     * @param  string $string
     * @return Diff[]
     */
    public function __construct($string)
    {
        $this->lines     = $this->splitOnNewLine($string, false);
        $this->lineCount = count($this->lines);
        
    }
    
    public function parse(){

        $diffs     = array();
        $diff      = null;
        $collected = array();
        
        for ($i = 0; $i < $this->lineCount; ++$i) {
            if (preg_match('(^---\\s+(?P<file>\\S+))', $this->lines[$i], $matchFileA) &&
                preg_match('(^\\+\\+\\+\\s+(?P<file>\\S+))', $this->lines[$i + 1], $matchFileB)) {
                
                //Second iteration
                if(count($collected) > 0 && count($diffs) > 0){
                    $lastDiff = end($diffs);
                    $diffLines = $this->parseDiffLines($collected);
                    $lastDiff->setDiffLines($diffLines);
                    reset($diffs);
                }
                
                //All iteration
                $diff = new GitDiff();
                $diff->setFileA($matchFileA['file']);
                $diff->setFileB($matchFileB['file']);
                $diffs[] = $diff;
                
                $collected = array();
                
                ++$i;
            } else {
                if (preg_match('/^(?:diff --git |index [\da-f\.]+|[+-]{3} [ab])/', $this->lines[$i])) {
                    continue;
                }
                $collected[] = $this->lines[$i]; 
            }
        }

        if(count($collected) > 0 && count($diffs) > 0){
            $lastDiff = end($diffs);
            $diffLines = $this->parseDiffLines($collected);
            $lastDiff->setDiffLines($diffLines);
            reset($diffs);
        }
        
        return $diffs;
    }
    
    /**
     * @param Diff  $diff
     * @param array $lines
     */
    private function parseDiffLines(array $lines)
    {
        $section = array();
        $diffLines = array();

        foreach ($lines as $line) {
            $diffLine = new GitDiffLine($line);
            if (preg_match('/^@@\s+-(?P<start>\d+)(?:,\s*(?P<startrange>\d+))?\s+\+(?P<end>\d+)(?:,\s*(?P<endrange>\d+))?\s+@@/', $line, $match)) {
                $section = array(
                    $match['start'],
                    isset($match['startrange']) ? max(1, $match['startrange']) : 1,
                    $match['end'],
                    isset($match['endrange']) ? max(1, $match['endrange']) : 1
                );
                $diffLine->setLineNumber('...');
                $lineNumber = $match['start'];
            }else{
                if($diffLine->getType() === GitDiffLine::REMOVED){
                    $diffLine->setLineNumber('');
                }else{
                    $diffLine->setLineNumber($lineNumber);
                    $lineNumber++;
                }
            }
            $diffLines[] = $diffLine;
        }
            
        return $diffLines; 
    }
    
    /**
     * Splits a block of text on newlines and returns an array
     *  
     * @param string $text Text to split
     * @param boolean $trimSpaces If true then each line is trimmed of white spaces. Default true. 
     * @return array Array of lines
     */
    protected function splitOnNewLine($text,$trimSpaces = true){
        if(!trim($text)){
            return array();
        }
        $lines = preg_split('/$\R?^/m', $text);
        if($trimSpaces){
            return array_map('trim',$lines); 
        }else{
            return $lines; 
        }
    }
}
