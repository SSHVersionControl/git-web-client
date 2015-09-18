Windows Git Installation
========================
You can download git for windows here: https://git-scm.com/download/win

When install make sure you install the command line version of Git.


Accessing SSH key.
----------------------------------

When git is run by apache/nginx on windows it runs the command as the "Local System Account".
This means that the Local System User Account needs access to the .ssh keys to pull push and clone from remote 
server, like github.com. Before continuing make sure you have git working with the local user (See).

Download PSTools (http://download.sysinternals.com/files/PSTools.zip)
Extract to a conventiant location.

Open cmd (Command Prompt) as an Administrator

    cd to PSTools location

Start the PsExec tool by running:

    PsExec.exe -i -s cmd.exe

Now we need to find out where this user’s home directory is:

    echo %userprofile%

For me this location was:

    C:\Windows\system32\config\systemprofile

Now you can open Git Bash within this shell by running the following command:

    32bit:  C:\Program Files (x86)\Git\bin\sh –login –i

    64bit:  C:\Program Files\Git\bin\sh –login –i

You can copy your existing private key into the correct location for the Local System User:

    cd ~
    mkdir -p /home/SYSTEM/.ssh
    cp /c/Users/.../.ssh/id_rsa ~/.ssh/

Finally you can test that you can successfully authenticate to remote server as the Local System User:

    ssh -T git@github.com

If all is successful you should see the usual message: