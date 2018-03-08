#!/usr/bin/python

# include RPi libraries in to Python code
import time, math
import RPi.GPIO as GPIO

# Electronics schema
#
#                  R=resistance   Rth=getValue()
# a_pin (charge) ---/\/\/\--------/\/\/\--.
#                                         |
#                      R=~1k              |   C=capacitance
# b_pin (discharge) ---/\/\/\-------------+---||--.
#                                                 |
#                                                 V Gnd
#
class ThermistorInterface:
    """
    Implement methods to compute the actual resistance of a thermistor by means
    of discharge and charge timings of a series capacitor, using two GPIO pins.
    """
    a_pin = None
    b_pin = None
    discharge_time = 1
    discharged = None

    capacitance = 0.00001
    resistance = 4700
    supply = 3.3
    threshold = 1.3

    model = None

    def __init__ (self, charge, discharge, thermistor):
        self.a_pin = charge
        self.b_pin = discharge
        GPIO.setwarnings(False)
        GPIO.setmode(GPIO.BCM)
        self.model = thermistor

    def discharge (self):
        GPIO.setup(self.a_pin, GPIO.IN)
        GPIO.setup(self.b_pin, GPIO.OUT)
        GPIO.output(self.b_pin, False)
        time.sleep(self.discharge_time)
        self.discharged = True

    def getChargeTime (self):
        self.discharged = False
        GPIO.setup(self.b_pin, GPIO.IN)
        GPIO.setup(self.a_pin, GPIO.OUT)
        max_count = 100
        count = 0
        GPIO.output(self.a_pin, True)
        start = time.time()
        while not GPIO.input(self.b_pin) and count < max_count:
            count = count +1
        return time.time() - start

    def getValue (self, time=None):
        if time is None:
            if not self.discharged:
                self.discharge()
            time = self.getChargeTime()
        num = -time
        div = self.capacitance * math.log(1- self.threshold / self.supply)
        return num / div - self.resistance

    def getTemperature (self):
        return self.model.getTemperature(self.getValue())


class TableThermistor:

    table = [[25, 100000]]

    def __init__ (self, table):
        self.table = table

    def getTemperature (self, resistance):
        asc =  (self.table[len(self.table)-1][1] - self.table[0][1]) > 0

        for i in range(0, len(self.table)-1):
            if asc:
                if self.table[i][1] > resistance:
                    if i > 0:
                        return resistance * (self.table[i][0] - self.table[i-1][0]) / (self.table[i][1] - self.table[i-1][1])
                    else:
                        return self.table[i][0]
            else:
                if self.table[i][1] < resistance:
                    if i > 0:
                        return (resistance - self.table[i][1]) * (-self.table[i][0] + self.table[i-1][0]) / (-self.table[i][1] + self.table[i-1][1]) + self.table[i][0]
                    else:
                        return self.table[i][0]

        return self.table[len(self.table)-1][0]


#if __name__ == '__main__':
    #therm = TableThermistor([
        #[10, 201659],
        #[15, 158499],
        #[20, 125468],
        #[25, 100000],
        #[30,  80223],
        #[35,  64759],
        #[40,  52589],
        #[45,  42951]
    #])
    #adi = ThermistorInterface(19, 13, therm)

    ## provide a loop to display analog data count value on the screen
    #while True:
        #print adi.getTemperature()
        #time.sleep(1)
