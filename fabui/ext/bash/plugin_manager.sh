#!/bin/bash

source /etc/default/fabui

CMD=$1
PLUGIN=$2

echo "Activating $1"

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
        rm -f ${FABUI_PATH}/application/controllers/Plugin_${PLUGIN}.php
        rm -f ${FABUI_PATH}/fabui/application/views/plugin/${PLUGIN}
        rm -f ${FABUI_PATH}/fabui/assets/plugin/${PLUGIN}
        ;;
    remove)
        echo "Removing $2"
        ${0} deactivate $PLUGIN
        rm -rf ${PLUGINS_PATH}/${PLUGIN}
        ;;
    add)
        ;;
    *)
        echo "Unknown command"
        ;;
esac
