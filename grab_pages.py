from bs4 import BeautifulSoup as bs
import requests
import time
import re
import MySQLdb

#already_visited = []

db = MySQLdb.connect(host="localhost",
                     user="meats",
                     passwd="d7bxqEGKtk",
                     db="meats")
cursor = db.cursor()
base_url = "https://www.growandbehold.com/"
cursor.execute("CREATE TABLE IF NOT EXISTS `meats`.`growandbehold` ( `name` VARCHAR(255), `price` DOUBLE, `unit` TEXT, `available` TEXT, `link` TEXT, `date` DATE, PRIMARY KEY (`name`, `date`));")
db.commit()

def main():

    start_time = time.time()

    # grab cleaned up main page
    global base_url
    soup = bs(getPage(base_url), "html.parser")

    # get anchor list
    nav = soup.find("div", attrs={"id" : "menu"})
    anchors = nav.find_all("a")
    links = []
    for anchor in anchors:
        if "https" in anchor['href']:
            links.append(anchor)

    # remove all duplicate hrefs to get rid of all chicken, all beef, etc
    anchors = []
    dupAnchors = []
    for link in links:
        if link['href'] not in anchors:
            anchors.append(link['href'])
        else:
            dupAnchors.append(link['href'])
    links = []
    for anchor in anchors:
        if anchor not in dupAnchors:
            links.append(anchor)

    all_prices = {}
    for link in links:
        for page in get_pages(link):
            flag = True
            for key in all_prices:
                if page in all_prices[key]:
                    flag = False
            if flag:
                prices = get_prices(page)
                for product in prices:
                    if len(prices[product]) == 4:
                        print(str(db.escape_string(product))[2:-1].replace("\\\\", "\\"))
                        sql = 'REPLACE INTO `meats`.`growandbehold` VALUES("' + str(db.escape_string(product))[2:-1].replace("\\\\", "\\") + '", "' + prices[product][0] + '", "' + prices[product][1] + '", "' + prices[product][2] + '", "' + prices[product][3] + '", ' + 'CURDATE());' 
                        print(sql)
                        cursor.execute(sql)
                        db.commit()

def get_prices(link):
    soup = bs(getPage(link), "html.parser")
    prices = dict()
    cards = soup.find_all("h4", attrs={"class" : "card-title h5"})
    for card in cards:
        anchor = card.find("a")
        try:
            price = get_price(anchor['href'])
            title = str(anchor).split(">")[1].split("<")[0]
            if price and title:
                prices[title.replace('"', '\"')] = price
        except Exception as error:
            pass
    return prices

def get_price(link):
    page = getPage(link)

    availability = "yes"
    if page.split('<meta property="og:availability" content="')[1].split('"')[0] == "oos":
        availability = "no"

    try:
        price = getFloat(page.split('<p class="productView-details__promotion">')[1].split("$")[1].split('/')[0])
        unit = getUnit(page.split('<p class="productView-details__promotion">')[1].split('/')[1].split("<")[0])
        return [price, unit, availability, link]
    except:
        raise Exception(link)
        return

def getUnit(val):
    units = ["oz", "lb"]
    for unit in units:
        if unit in val:
            return val.split(unit)[0] + unit
    return "unit"

def getFloat(val):
    ret = ""
    numeric = ".0123456789"
    for char in val:
        if char not in numeric:
            break
        else:
            ret += char
    return ret

def get_pages(link):
    global base_url
    soup = bs(getPage(link), "html.parser")

    page_list = soup.find("li", attrs={"class" : "pagination-item"})
    if page_list:
        next_page = soup.find("li", attrs={"class" : "pagination-item pagination-item--next"})

        if next_page:
            return [link] + get_pages(next_page.find_all("a")[0]['href'])
        else:
            return [link]

    else:
        return [link]

def getPage(url):
   # return os.popen('curl -s -S "' + url + '"', "r").read()[1:-1]
   # if url not in already_visited:
   #     already_visited.append(url)
   #     return str(requests.get(url).content)
   # else:
   #     return ""
    return str(requests.get(url).content)
main()
