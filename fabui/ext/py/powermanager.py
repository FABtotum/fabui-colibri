#!/usr/bin/python
# coding=utf8
#
# File: powermanager.py
#
#   Shutdown/reboot(/power on) Raspberry Pi with pushbutton
#
# Description:
#   The daemon is configured to start and stop by default,
# but it won't do nothing without proper configuraion lines
# under the [power] section of configuration.ini.
#
#   The configuration for TOTUMduino v3 is:
#
#| switch_pin = 4
#| ps_on_pin=8
#
#   On stand-alone Prism the following can be used:
#
#| switch_pin = 26
#

import RPi.GPIO as GPIO
from subprocess import call
from signal import SIGTERM, signal
import time
from threading import Timer

from fabtotum.fabui.config              import ConfigService

# pushbutton connected to this GPIO pin, using pin 5 also has the benefit of
# waking / powering up Raspberry Pi when button is pressed
checkPin = None
actionPin = None

# if button pressed for at least this long then shut down. if less then reboot (not implemented).
shutdownSeconds = 3
bootSeconds = 0.5
# button debounce time in seconds
# using timers rather than polling for the button state, a low value for
# bouncetime is advisable (just to filter out some noise in transitions)
# but not too short as this is a human operated input afterall so
# we can expect imprecise or shaky "actuation"
debounceMilliseconds = 10

#buttonPressedTime = None
powerTimer = None
power = None

lastButtonValue = True

terminated = False

def boot ():
    global power, actionPin, powerTimer

    print "PowerManager: boot"
    GPIO.output(actionPin, GPIO.HIGH)
    power = True
    powerTimer = None
    # After soft-boot restart fabui to let it recognize the fablin
    call(['/etc/init.d/fabui', 'restart'])

def shutdown ():
    global power, actionPin, powerTimer

    print "PowerManager: shutdown"
    power = False
    powerTimer = None
    call(['reboot'])
    # Actual power-down will be done in shutdown script

def signalTermination (a, b):
    global power, powerTimer, actionPin, terminated

    print "PowerManager: terminating"
    powerTimer = None
    power = False
    if actionPin is not None:
        GPIO.output(actionPin, GPIO.LOW)
    terminated = True

def powerButtonPressed ():
    global power, powerTimer
    global shutdownSeconds, bootSeconds

    if powerTimer is not None: return
    if power:
        powerTimer = Timer(shutdownSeconds, shutdown)
        print "Start power-off Timer"
    else:
        powerTimer = Timer(bootSeconds, boot)
        print "Start power-on Timer"
    powerTimer.start()

def powerButtonReleased ():
    global power, powerTimer

    if powerTimer is not None:
        if power: print "Cancel power-off Timer"
        else: print "Cancel power-on Timer"
        powerTimer.cancel()
        powerTimer = None

def powerButtonEvent (pin):
    global power, lastButtonValue

    buttonValue = GPIO.input(pin)
    print "Button:", buttonValue

    # Se lo stato del pulsante non cambiato dall'ultima invocazione
    # non bisogna fare niente
    if buttonValue == lastButtonValue: return

    power = GPIO.input(actionPin)
    if buttonValue: powerButtonReleased()
    else: powerButtonPressed()

    lastButtonValue = buttonValue


if __name__ == '__main__':
    GPIO.setmode(GPIO.BCM)

    config = ConfigService()

    try:
        checkPin = int(config.get('power', 'switch_pin'))
    except:
        print "PowerManager: no power switch configured"
    else:
        print "PowerManager: switch_pin={}".format(checkPin)

    try:
        actionPin = int(config.get('power', 'ps_on_pin'))
    except:
        print "PowerManager: no power supply pin configured"
    else:
        print "PowerManager: ps_on_pin={}".format(actionPin)

    if checkPin is not None:
        GPIO.setup(checkPin, GPIO.IN, pull_up_down=GPIO.PUD_UP)
        # subscribe to button presses
        GPIO.add_event_detect(checkPin, GPIO.BOTH, callback=powerButtonEvent, bouncetime=debounceMilliseconds)

    if actionPin is not None:
        GPIO.setup(actionPin,GPIO.OUT)
        power = GPIO.input(actionPin)
        print "PowerManager: pwr={}".format(power)

    # Catch and process SIGTERM
    signal(SIGTERM, signalTermination)

    # Monitor soft power button until terminated
    while not terminated:
        # sleep to reduce unnecessary CPU usage
        time.sleep(0.1)

    GPIO.cleanup()
