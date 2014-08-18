#!/usr/bin/env python
# encoding: utf-8
# api: cli
# type: main
# title: freecode-to-releases     
# description: Extracts project descriptions+history from Freecode.com into releases.JSON
# category: scraping
# version: 0.5
# config:
#   <env-unused name=XDG_CONFIG_HOME value=~/.config description="user config base dir"> 
# license: MITL
# doc: http://fossil.include-once.org/freshcode/wiki/freecode2releases
# 
#
# Fetches prior freshmeat/freecode.com project listings, and extracts   
# the version history. Packages data up as `releases.json` format, which  
# makes a nice backup format and exchange format. And also suits importing
# into freshcode.club listings.
#
# Notably this should only be done by package maintainers to retain their
# original authorship and thus reusability.
#

import sys
import errno
import collections
from datetime import datetime
import re
import requests
try:
    from bs4 import BeautifulSoup as bs
except:
    print("f2r: BeatifulSoup missing,\nuse `apt-get install python-bs4` || `pip install beautifulsoup4`\n")
    exit(errno.ENOPKG)
try:
    import simplejson as json
except:
    import json



# scrape from freecode.com
def freecode_fetch(name):
    url = "http://freecode.com/projects/%s" % name
    html = bs(requests.get(url).text)
    # le basics
    r = collections.OrderedDict([
        ("$feed-license", "CC author/editors"),
        ("$feed-origin", url),
        ("name", name),
        ("title", html.find("meta", {"property": "og:title"})["content"]),
        ("oneliner", html.find("meta", {"property": "og:description"})["content"]),
        #("image", "http://freshcode.com" + html.find("meta", {"property": "og:image"})["content"]),
        ("keywords", html.find("meta", {"name": "keywords"})["content"]),
        ("description", html.select("div.project-detail p")[0].string),
        ("releases", freecode_releases(name)),
    ])
    return r


# fetch releases pages
def freecode_releases(name):
    last_page = 1
    page = 0
    r = []
    while page <= last_page:
        # iterate through /releases pages
        url = "http://freecode.com/projects/%s/releases%s" % (name, ("?page=%s" % page if page else ""))
        html = bs(requests.get(url).text)
        for ver in html.select("div.release.clearfix"):
            # remove changelog gimmicks
            for rm in ("strong", ".truncate_ellipsis", ".truncate_more_link"):
                for e in ver.select(rm):
                    e.replace_with("");
            # collect
            r.append({
                "version": ver.select("li.release a")[0].string,
                "changes": "".join(ver.select("p.truncate.changes")[0].contents).strip(),
                "scope": "incremental",
                "state": "historic",
                "published": strftime(ver.select("li.rdate")[0].string.strip()).isoformat(),
                "submitter": ver.select(".author a")[0]["title"]
            })
        # next page
        try:
            last_page = int(html.select(".pagination a")[-2].string)
        except:
            last_page = 1
        page = page + 1
        print page
    return r


# try to deduce time from different formats
def strftime(s):
    for fmt in [
        "%d %b %Y %H:%M",
        "%Y-%m-%d %H:%M"
    ]:
        try:
            return datetime.strptime(s, fmt)
        except:
            None
    pass


# process CLI arguments, invoke retrieval methods
def freecode_cli(argv0="f2r", name="", output="releases.json"):
    if name:
        json.dump(freecode_fetch(name), open(output, "wt"), indent=4)
    else:
        print("synopsis: freecode2releases.py [projectname [output.json]]");
        print("[31mPlease only download and resubmit project data you initially wrote yourself.[0m");
    return


# argv to main
if __name__ == "__main__":
    freecode_cli(*sys.argv)


