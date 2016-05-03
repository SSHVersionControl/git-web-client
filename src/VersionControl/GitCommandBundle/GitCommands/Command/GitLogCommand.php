<?php

/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitCommandBundle\GitCommands\Command;

use VersionControl\GitCommandBundle\Entity\GitLog;


/**
 * 
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitLogCommand extends AbstractGitCommand {
    
    /**
     *
     * @var String Log format 
     */
    protected $format = '%H | %h | %T | %t | %P | %p | %an | %ae | %ad | %ar | %cn | %ce | %cd | %cr | %s';
    
    /**
     * Limit the number of commits return from command. 
     * @var integer 
     */
    protected $logCount = 9999999;
    
    /**
     * Pagintation: Number of results to return
     * @var integer 
     */
    protected $limit = 30;
    
    /**
     * Pagintation: Current Page
     * @var integer 
     */
    protected $page = 0;
    
    /**
     * Pagintation: Total Number of results
     * @var integer 
     */
    protected $totalCount = 0;
    
    /**
     * Pagintation: Total Number of pages
     * @var integer 
     */
    protected $totalPages = 0;
    
    /**
     * Git log command 
     * @var string 
     */
    protected $logCommand;
    
    /**
     * Filter log by commit hash. Only returns logs with this hash. Should be only one result.
     * @var string 
     */
    protected $commitHash;
    
    /**
     * Filter log by branch name. Only returns commits form this branch
     * @var string 
     */
    protected $branch;
    
    /**
     * Enables the --not --remotes flags. Returns commits that have not been
     * pushed to a remote server.
     * 
     * @var boolean 
     */
    protected $notRemote;
    
    /**
     * Enables the --decorate flag. The --decorate flag makes git log display all of the 
     * references (e.g., branches, tags, etc) that point to each commit.
     * 
     * This lets you know that the top commit is also checked out (denoted by HEAD) and 
     * that it is also the tip of the master branch. The second commit has another branch 
     * pointing to it called feature, and finally the 4th commit is tagged as v0.9.
     * 
     * Branches, tags, HEAD, and the commit history are almost all of the information contained 
     * in your Git repository, so this gives you a more complete view of the logical 
     * structure of your repository.
     * @var boolean 
     */
    protected $showReferences;
    
    /**
     * Array of log lines.
     * @var array 
     */
    protected $logData = array();
    
    /**
     * Filter By Message
     * 
     * To filter commits by their commit message, use the --grep flag. 
     * @var string 
     */
    protected $filterByMessage;
    
    /**
     * Filter by Date After
     * If you’re looking for a commit from a specific time frame, you can use 
     * the --after or --before flags for filtering commits by date. 
     * These both accept a variety of date formats as a parameter. 
     *      git log --after="2014-7-1"
     *      git log --after="yesterday"
     *      git log --after="2014-7-1" --before="2014-7-4"
     * 
     * @var string 
     */
    protected $filterByDateAfter;
    
    /**
     * Filter by Date After
     * If you’re looking for a commit from a specific time frame, you can use 
     * the --after or --before flags for filtering commits by date. 
     * These both accept a variety of date formats as a parameter. 
     *      git log --after="2014-7-1"
     *      git log --after="yesterday"
     *      git log --after="2014-7-1" --before="2014-7-4"
     * 
     * @var string 
     */
    protected $filterByDateBefore;
    
    /**
     * Filter By Author
     * When you’re only looking for commits created by a particular user, use the --author flag. 
     * This accepts a regular expression, and returns all commits whose author matches that 
     * pattern. If you know exactly who you’re looking for, you can use a plain old string 
     * instead of a regular expression: --author="John"
     *
     * @var string 
     */
    protected $filterByAuthor;
    
    /**
     * Filter By Content
     * Used to search for commits that introduce or remove a particular line of source code. 
     * This is called a pickaxe, and it takes the form of -S"<string>". For example, if you 
     * want to know when the string Hello, World! was added to any file in the project, 
     * you would use the following command: 
     *      git log -S"Hello, World!"
     * 
     * @var string 
     */
    protected $filterByContent;
    
    
    
    public function getLogCommand(){
        
        $this->logCommand = 'git --no-pager log -m "--pretty=format:\''.$this->format.'\'"';
        if($this->logCount){
            $this->logCommand .= ' -'.intval($this->logCount).' ';
        }
        
        if($this->showReferences === true){
            $this->logCommand .= ' --decorate ';
        }
        
        if($this->commitHash && $this->branch == false){
            $this->logCommand .= ' '.escapeshellarg($this->commitHash);
        }
        
        //Filters
        if($this->filterByMessage){
           $this->logCommand .= ' --grep='.escapeshellarg($this->filterByMessage).' -i';
        }
        
        if($this->filterByDateAfter){
           $this->logCommand .= ' --after='.escapeshellarg($this->filterByDateAfter);
        }
        
        if($this->filterByDateBefore){
           $this->logCommand .= ' --before='.escapeshellarg($this->filterByDateBefore);
        }
        
        if($this->filterByAuthor){
           $this->logCommand .= ' --author='.escapeshellarg($this->filterByAuthor);
        }
        
        if($this->filterByContent){
           $this->logCommand .= ' -S'.escapeshellarg($this->filterByContent);
        }
        
        if($this->branch){
            //Need to append -- do tell git that its a branch and not a file
            $this->logCommand .= ' '.escapeshellarg(trim($this->branch)).' --';
        }else{
            $this->logCommand .= ' --all';
        }
        
        if($this->notRemote){
            $this->logCommand .= ' --not --remotes';
        }

        return $this->logCommand;
    }
    
    /**
     * Executes the git log command
     * 
     * @return GitLogCommand
     */
    public function execute(){
        $logs = array();
        try{
            $logCommand= $this->getLogCommand();
            $this->logData = $this->command->runCommand($logCommand);
            
        }catch(\RuntimeException $e){
            if($this->getObjectCount() == 0){
                $this->logData = '';
            }else{
                //Throw exception
                throw new \Exception("Error in getting log: ".$e->getMessage());
            }
        }
        
        return $this;
    }
    
    /**
     * Gets the results. Runs any pagination on the log data that is need and 
     * converts the log lines into GitLog objects.
     * 
     * @return array of GitLog objects
     */
    public function getResults(){
        $logs = array();
        if(is_array($this->logData)){
            return array();
        }
        $lines = $this->splitOnNewLine($this->logData);

        if(is_array($lines)){
            $paginatedLines = $this->paginate($lines);
            foreach($paginatedLines as $line){
                if(trim($line)){
                    $logs[] = new GitLog($line);
                }
            }
        }
        
        return $logs;
    }
    
    /**
     * Gets the first result. Does not run pagination.
     * Used mostly for commit hash data
     * converts the log lines into GitLog objects.
     * 
     * @return GitLog
     */
    public function getFirstResult(){
        $log = null;
        $lines = $this->splitOnNewLine($this->logData);

        if(is_array($lines) && count($lines) > 0 ){
            $line = trim($lines[0]);
            $log = new GitLog($line);
        }
        
        return $log;
    }
    
    /**
     * Paginates log lines using array_slice.
     * 
     * @param array $lines
     * @return array of lines
     */
    protected function paginate($lines){
        $this->totalCount = count($lines);
        $this->totalPages = ceil( $this->totalCount/ $this->limit );
        
        $page = min($this->page, $this->totalPages );
        $splitOn = $page * $this->limit;
        
        return array_slice($lines, $splitOn, $this->limit);
    }
    
    public function getLogCount() {
        return $this->logCount;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function getPage() {
        return $this->page;
    }

    public function getTotalCount() {
        return $this->totalCount;
    }

    public function getTotalPages() {
        return $this->totalPages;
    }

    public function getCommitHash() {
        return $this->commitHash;
    }

    public function getBranch() {
        return $this->branch;
    }

    public function getNotRemote() {
        return $this->notRemote;
    }

    public function getShowReferences() {
        return $this->showReferences;
    }

    public function getFilterByMessage() {
        return $this->filterByMessage;
    }

    public function getFilterByDateAfter() {
        return $this->filterByDateAfter;
    }

    public function getFilterByDateBefore() {
        return $this->filterByDateBefore;
    }

    public function getFilterByAuthor() {
        return $this->filterByAuthor;
    }

    public function getFilterByContent() {
        return $this->filterByContent;
    }

    public function setLogCount($logCount) {
        $this->logCount = $logCount;
        return $this;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function setPage($page) {
        $this->page = $page;
        return $this;
    }

    public function setTotalCount($totalCount) {
        $this->totalCount = $totalCount;
        return $this;
    }

    public function setTotalPages($totalPages) {
        $this->totalPages = $totalPages;
        return $this;
    }

    public function setCommitHash($commitHash) {
        $this->commitHash = $commitHash;
        return $this;
    }

    public function setBranch($branch) {
        if($branch !== '(No Branch)'){
            $this->branch = $branch;
        }
        return $this;
    }

    public function setNotRemote($notRemote) {
        $this->notRemote = $notRemote;
        return $this;
    }

    public function setShowReferences($showReferences) {
        $this->showReferences = $showReferences;
        return $this;
    }

    public function setFilterByMessage($filterByMessage) {
        $this->filterByMessage = $filterByMessage;
        return $this;
    }

    public function setFilterByDateAfter($filterByDateAfter) {
        $this->filterByDateAfter = $filterByDateAfter;
        return $this;
    }

    public function setFilterByDateBefore($filterByDateBefore) {
        $this->filterByDateBefore = $filterByDateBefore;
        return $this;
    }

    public function setFilterByAuthor($filterByAuthor) {
        $this->filterByAuthor = $filterByAuthor;
        return $this;
    }

    /**
     * 
     * @param type $filterByContent
     * @return GitLogCommand
     */
    public function setFilterByContent($filterByContent) {
        $this->filterByContent = $filterByContent;
        return $this;
    }


    
}