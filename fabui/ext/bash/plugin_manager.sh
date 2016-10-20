#!/bin/bash
################################################################################
# @author FABteam (C) 2016 
# @license https://opensource.org/licenses/GPL-3.0
################################################################################

source /etc/default/fabui

CMD=$1
PLUGIN=$2

extract_plugin() {
    FN=$1
    DST=$2
    EXT=${FN##*.}
    case $EXT in
        zip)
            unzip $FN -d $DST -o &> /dev/null
            ;;
        #~ gz)
            #~ ;;
        #~ bz2)
            #~ ;;
        #~ xz)
            #~ ;;
        #~ tgz)
            #~ ;;
    esac
}

install_plugin()  {
    FN=$1
    TMP="${TEMP_PATH}new_plugin"
    mkdir -p $TMP
    
    extract_plugin $FN $TMP

    HAVE_META=no    

    # Find out what the top directory is
    TOP=$(ls $TMP)
    PLUGIN=$(basename $FN | tr '[:upper:]' '[:lower:]')
    PLUGIN=${PLUGIN%.*}
    
    if [ -d "$TMP/$TOP" ]; then
        TOP=$TMP/$TOP
    else
        TOP=$TMP
    fi
    
    if [ -f "$TOP/meta.json" ]; then
        HAVE_META=yes
        
        PLUGIN_DIR=${PLUGINS_PATH}${PLUGIN}
        echo $PLUGIN_DIR
        mkdir -p $PLUGIN_DIR
        
        cp -aR $TOP/* $PLUGIN_DIR
        
        rm -rf $TMP
        rm $FN
    else
        echo "Not a plugin .zip file."
        exit 1
    fi
}

case $CMD in
    activate)
        echo "Activating $2"
        ln -sf ${PLUGINS_PATH}/${PLUGIN}/controller.php  ${FABUI_PATH}application/controllers/Plugin_${PLUGIN}.php
        mkdir -p ${FABUI_PATH}application/views/plugin
        ln -sf ${PLUGINS_PATH}/${PLUGIN}/views           ${FABUI_PATH}application/views/plugin/${PLUGIN}
        mkdir -p ${FABUI_PATH}assets/plugin
        ln -sf ${PLUGINS_PATH}/${PLUGIN}/assets          ${FABUI_PATH}assets/plugin/${PLUGIN}
        ;;
    deactivate)
        echo "Deactivating $2"
        rm -f ${FABUI_PATH}application/controllers/Plugin_${PLUGIN}.php
        rm -f ${FABUI_PATH}application/views/plugin/${PLUGIN}
        rm -f ${FABUI_PATH}assets/plugin/${PLUGIN}
        ;;
    remove)
        echo "Removing $2"
        ${0} deactivate $PLUGIN
        rm -rf ${PLUGINS_PATH}/${PLUGIN}
        ;;
    install)
        echo "Installing from '$2' file"
        install_plugin $PLUGIN
        ;;
    *)
        echo "Unknown command"
        ;;
esac
