#-*- coding:gbk -*-
# 导入开发包
import sys
from urllib.request import urlopen
from bs4 import BeautifulSoup as bs
from urllib.request import Request
from urllib import parse
import re
import random
import json
req=Request("http://h5.imanm.com/movie2/ajax/cinamershow")
if len(sys.argv[4])==10:
  postData=parse.urlencode([
  ("cinemanid",sys.argv[1]),
  ("cinemaname",parse.unquote(sys.argv[2])),
  ("movieid",sys.argv[3]),
  ("currentDay",sys.argv[4]),
  ("sign",sys.argv[5])
  ])
else:
  postData=parse.urlencode([
  ("cinemanid",sys.argv[1]),
  ("cinemaname",parse.unquote(sys.argv[2])),
  ("movieid",sys.argv[3]),
  ("sign",sys.argv[4])
  ])
#写文件读取分析
postData=postData
req.add_header("Host","h5.imanm.com")

req.add_header("Proxy-Connection","keep-alive")
req.add_header("Accept","text/html")
req.add_header("Origin","http://h5.imanm.com")
req.add_header("X-Requested-With","XMLHttpRequest")  
req.add_header("User-Agent","Mozilla/5.0 (iPhone; CPU iPhone OS 8_1_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B466 MicroMessenger/6.5.5 NetType/WIFI Language/zh_CN Accept-Language: zh-cn")
req.add_header("Content-Type","application/x-www-form-urlencoded")
req.add_header("Referer","http://h5.imanm.com/movie2/cinamermoviedetails/1171/784")    
req.add_header("Accept-Language","zh-CN,zh;q=0.8")
req.add_header("Cookie","WR_PROXY_AUTH=C18C6503D83B514371E9ECBC77D5F065; ci_session=df68f708a11b126c6bdbbfe7b36ea1dfad21dfde; uid=3298607")      
resp=urlopen(req,data=postData.encode('utf-8'))
soup=bs(resp,"html.parser")
break_flag=False
tags = soup.findAll('li', class_="mui-table-view-cell mui-collapse")
showlist=[]
datalist=[]
for tag in tags:
    i=0
   #print(tags)
    show_tag={}
            #开始时间
    start_time=tag.find('div',class_="cinamer-details-playshow-time").find('h4')
            #starttime=start_time.get_text()
            
    show_tag['start_time']=start_time.get_text()
            #结束时间
    stop_time=tag.find('div',class_="cinamer-details-playshow-time").find('h5')
            #stoptime=stop_time.get_text()
    show_tag['stop_time']=stop_time.get_text()
            #类型
    type=tag.find('div',class_="cinamer-details-playshow-type").find('h4')
    show_tag['type']=type.get_text()
            #放映厅
    location=tag.find('div',class_="cinamer-details-playshow-type").find('h5')
    show_tag['location']=location.get_text()
    #cinamer-details-playshow-price-platform
    paritynum=tag.find('div',class_="cinamer-details-playshow-price-platform").get_text()
    show_tag['paritynum']=paritynum
    items=tag.findAll('li', class_="mui-table-view-cell")
    
    j=len(items)
    #showlist.append(show_tag)
    #print(showlist)
    showparity=[]
    list={}
    for item in items:
              parity={}
              item_name=item.find('div',class_="cinamer-details-playshow-item-name").find('span')
              #itemname=item_name.get_text()
              parity['name']=item_name.get_text().strip()
              item_price=item.find('ul',class_="changci-price").find('li')
              parity['price']=item_price.get_text()
              item_price2=item.find('ul',class_="changci-price").findAll('li')
              showprice=item_price2[1].get_text()
              #parity['showlist']=showlist
              #itemprice=item_price.get_text()
              parity['showprice']=showprice
              icon=item.find('div',class_="cinamer-details-playshow-item-icon").find('img').get("src")
              parity['icon']=icon
              if parity['name']=='票贩代购':
                j=j-1
                show_tag['paritynum']=str(j)
              if parity['name']!='票贩代购' and parity['name']!='团购券':
                showparity.append(parity)
              #showlist['list']=showparity
    #print(item)
              #print(showlist)
              #print("<----->","timestart:"+starttime)
              #print("<----->","timestop:"+stoptime)
              #print("<----->","itemname:"+itemname)
              #print("<----->","itemprice:"+itemprice)
              #print("<----->","cinemaid:"+sys.argv[1])
              #print("<----->","cinemaname:"+str(row["cinemaname"]))
              #print("<----->","cityid:"+str(row["cityid"]))
              #print("<----->","page:"+str(row["page"]))
    # 获取数据库连接
              #connection=pymysql.connect(host='localhost',
                   #user='root',
                   #password='root',
                   #db='movieurl',
                   #harset='utf8mb4')
   
              #try:
                #with connection.cursor() as cursor:
                 #sql="insert into`bijiaday`(starttime,stoptime,itemname,itemprice,cinemaid,cinemaname,cityid,page,currentday)values(%s,%s,%s,%s,%s,%s,%s,%s,%s)"
                 #cursor.execute(sql,(starttime,stoptime,itemname,itemprice,row["cinemaid"],row["cinemaname"],row["cityid"],row["page"],currentday))
                 #connection.commit()
              #if item_name=='票贩代购':
               # j=j-1
              
                i=i+1
    
              #finally:
                #connection.close()
      
                if i==j:
                   
                    break_flag=True
                  #print(showlist)
                    break 
    show_tag['parity']=showparity
    list['list']=show_tag
    showlist.append(list)            
#print (showlist)
json_str=json.dumps(showlist)
print(json_str)
