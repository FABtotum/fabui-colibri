#!/usr/bin/env python2

from __future__ import print_function
import os
import sys

def find_match(line, fn, ln):
	match = {}
	msgid = ""
	start = False
	i = 0
	while i < len(line):
		char = line[i]
		
		if start:
			seg = line[i:i+2]
			if (seg == '")' and start == '_("') or (seg == "')" and start == "_'("):
				start = ""
				if msgid not in match:
					match[msgid] = []
				match[msgid].append('#: {0}:{1}'.format(fn, ln))
				msgid = ""
			else:
				msgid += char
		else:
			seg = line[i:i+3]
			if seg == '_("' or seg == "_('":
				start = seg
				i+= 2
				
		i += 1
		
	return match

result = {}
for fn in sys.argv[1:]:
	with open(fn) as f:
		content = f.read()
		ln = 1
		for line in content.split('\n'):
			m = find_match( line, fn, ln )
			result.update(m)
			ln += 1

print('''# SOME DESCRIPTIVE TITLE.
# Copyright (C) YEAR FABtotum
# This file is distributed under the same license as the FABUI package.
# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.
#
#, fuzzy
msgid ""
msgstr ""
"Project-Id-Version: FABUI\\n"
"Report-Msgid-Bugs-To: \\n"
"POT-Creation-Date: 2017-02-17 14:35+0100\\n"
"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n"
"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n"
"Language-Team: LANGUAGE <LL@li.org>\\n"
"Language: \\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=CHARSET\\n"
"Content-Transfer-Encoding: 8bit\\n"
''')
for msgid in result:
	msgid = msgid.replace('"', '\"')
	for comment in result[msgid]:
		print(comment)
	print('msgid "{0}"'.format(msgid) )
	print('msgstr ""')
	print()
