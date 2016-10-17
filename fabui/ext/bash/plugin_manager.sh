#!/bin/bash

source /etc/default/fabui

CMD=$1
PLUGIN=$2

case $CMD in
    activate)
        ln -sf ${PLUGINS_PATH}/${PLUGIN}/controller.php  ${FABUI_PATH}application/controllers/Plugin_${PLUGIN}.php
        mkdir -p ${FABUI_PATH}application/views/plugins
        ln -sf ${PLUGINS_PATH}/${PLUGIN}/views           ${FABUI_PATH}application/views/plugins/${PLUGIN}
        mkdir -p ${FABUI_PATH}assets/plugins
        ln -sf ${PLUGINS_PATH}/${PLUGIN}/assets          ${FABUI_PATH}assets/plugins/${PLUGIN}
        ;;
    deactivate)
        rm -f ${FABUI_PATH}/application/controllers/Plugin_${PLUGIN}.php
        rm -f ${FABUI_PATH}/fabui/application/views/plugins/${PLUGIN}
        rm -f ${FABUI_PATH}/fabui/assets/plugins/${PLUGIN}
        ;;
    uninstall)
        ${0} deactivate $PLUGIN
        rm -rf ${PLUGINS_PATH}/${PLUGIN}
        ;;
    install)
        ;;
    *)
        ;;
esac
