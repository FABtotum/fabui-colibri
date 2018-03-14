#!/bin/env python
# -*- coding: utf-8; -*-
#
# (c) 2016 FABtotum, http://www.fabtotum.com
#
# This file is part of FABUI.
#
# FABUI is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# FABUI is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with FABUI.  If not, see <http://www.gnu.org/licenses/>.

__authors__ = "Marco Rizzuto, Daniel Kesler, Krios Mane"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import standard python module
import os
import json

# Import internal modules
from fabtotum.utils.translation import _, setLanguage


def prepare_prism(app, args=None, lang='en_US.UTF-8'):
    _ = setLanguage(lang)

    app.macro("M701 S0", "ok", 3, _("Turning off lights"))
    app.macro("M702 S0", "ok", 3, _("Turning off lights"), verbose=False)
    app.macro("M703 S0", "ok", 3, _("Turning off lights"), verbose=False)