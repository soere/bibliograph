# Install the latest version of Bibliograph

With the scripts in this directory, you can easily download, install and test
the latest version of Bibliograph (including alpha and beta versions). Your
currently running instance will not be touched. Instead, the new version will
be installed in a separate directory, and a copy of all data will be imported
into the new instance. This will allow you to extensively test the new version 
before going into production with it, and even after that, the old version is kept
fully functional until deleted.

By default, the installation package is downloaded from GitHub. If you want to use
a locally built package, provide its path via the `-F` or `--package-file` option.

## Install prerequisites

To install the prerequisites, run the script that matches your OS in the `setup` folder, if
it exists. If no such script exists, please contribute one. 

## Install/update application

In order to install a new version, please proceed as follows:

1. Configuration (`app.conf.toml`) 
  - If this is the first install: 
    Adapt `app.conf.toml` with the parameters that match your local installation
  - If you are updating the installation, check the release notes to se if you 
    have to modify `app.conf.toml` (usually not needed)
  - To edit the file, I recommend to install/activate a plugin for editing
    ".toml" file in your editor. This will allow to validate your changes and prevent errors. 
  - Please do not change the placeholders ("{{...}}") which are dynamically
    replaced by the install script
2. Run ./install.sh in this directory
3. Open a browser window at the URL that is displayed at the end of a successful
   installation. On a Mac, you can hold the "Command" Key and double-click on the
   URL. 
  