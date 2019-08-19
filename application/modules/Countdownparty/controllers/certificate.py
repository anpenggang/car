#!/usr/bin/python
# -*- coding: UTF-8 -*-
# @Date    : 2018-1-2 13:58:59
# @Author  : Leslie (lesliedream@outlook.com)
import sys,Image,ImageDraw,ImageFont
ttfont = ImageFont.truetype("/home/www/wxdanmu/public/fonts/STXIHEI.TTF",30)
im = Image.open("/home/www/wxdanmu/public/images/CdpCertificate/poster.png")
draw = ImageDraw.Draw(im)
draw.text((80,550),unicode(sys.argv[1],'utf-8'), fill=(255,255,255),font=ttfont)
im.save('/home/www/wxdanmu/public/images/CdpCertificate/'+sys.argv[2]+'.png')
#im.show()
