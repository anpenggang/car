#!/usr/bin/python
# -*- coding: UTF-8 -*-
# @Date    : 2018-3-28 15:34:59
# @Author  : Leslie (lesliedream@outlook.com)

import sys
import Image
import ImageDraw
import ImageFont
import urllib2
import cStringIO

ttfont = ImageFont.truetype("/home/www/wxdanmu/public/fonts/STXIHEI.TTF",60)

url = sys.argv[3];
file = cStringIO.StringIO(urllib2.urlopen(url).read())
im = Image.open(file)
draw = ImageDraw.Draw(im)
draw.text((35,240),unicode(sys.argv[1],'utf-8'), fill=(255,255,255),font=ttfont)
im.save('/home/www/wxdanmu/public/images/partnerInvite/'+sys.argv[2]+'.png')
#im.show()
