#!/bin/bash
# Create a bibliograph plugin
# needs to be called in the tool dir

PLUGIN_DIR=../bibliograph/plugins

cd ..
echo "This script creates a skeleton for a Bibliograph plugin."
echo
namespace=""
while [ -z $namespace ]; do
    echo -n "Please enter the namespace of the new plugin: "
    read namespace
    if [ -d $PLUGIN_DIR/$namespace ]; then
        echo
        echo "This namespace already exists. Please use another one."
        namespace=""
    fi
    if ! [[ $namespace =~ ^[a-zA-Z0-9]+$ ]]; then
        echo
        echo "Invalid input. Only ASCII characters and numbers are allowed."
        namespace=""
fi
done
echo ">>> Creating plugin '$namespace'..."

# copy skeleton
mkdir $PLUGIN_DIR/$namespace
cp -a $PLUGIN_DIR/template/* $PLUGIN_DIR/$namespace/

# rename directories and files
mv $PLUGIN_DIR/$namespace/services/class/template \
    $PLUGIN_DIR/$namespace/services/class/$namespace
mv $PLUGIN_DIR/$namespace/services/locale/C.UTF-8/LC_MESSAGES/template_en.po \
    $PLUGIN_DIR/$namespace/services/locale/C.UTF-8/LC_MESSAGES/${namespace}_en.po
mv $PLUGIN_DIR/$namespace/source/class/template \
    $PLUGIN_DIR/$namespace/source/class/$namespace 
mv $PLUGIN_DIR/$namespace/source/resource/template \
    $PLUGIN_DIR/$namespace/source/resource/$namespace

    
# replace "template" with namespace in files

find $PLUGIN_DIR/$namespace -type f -regex ".*/.*\.\(json\|js\|php\)" \
    -exec sed -i'' -e "s/template/$namespace/g" '{}' +
    
echo ">>> Skeleton for '$namespace'-Plugin has been created."
echo "    ------------------------------------------------------------------------"
echo "    If your plugin has a javascript frontend, please add the following code:"
echo "      config.json/jobs/libraries/library:"
echo "          { \"manifest\" : \"plugins/$namespace/Manifest.json\" },"
echo "      config.json/jobs/parts-config/packages/parts:"
echo "          \"plugin_$namespace\"  : { \"include\" : [ \"$namespace.Plugin\" ] },"
echo "      /source/class/bibliograph/PluginManager.js:after 'var plugins= {':"
echo "          \"plugin_$namespace\"  : window.$namespace ? $namespace.Plugin : null,"
echo "      Insert plugin initialization code in" 
echo "      /plugins/$namespace/source/class/$namespace/Plugin.js"
echo "    If no frontend is needed, you can delete the 'source' folder."
echo "    ------------------------------------------------------------------------"
echo "    If your plugin provides JSONRPC services, please configure the routes in"
echo "    /plugins/$namespace/services/class/$namespace/routes.php"
echo "    ------------------------------------------------------------------------"
echo "    To activate the plugin and make it visible to the plugin manager,"
echo "    change the \$visible property to true in"
echo "    /plugins/$namespace/services/class/$namespace/Plugin.php"
echo
