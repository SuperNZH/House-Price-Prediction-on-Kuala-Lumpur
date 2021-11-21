#!/usr/bin/env python
# -*- coding: utf-8 -*-
import time
import datetime
__version__ = '1.0.0.0'


class DateUtil(object):

    @staticmethod
    def timestamp_to_string(timestamp, str_format="%Y-%m-%d %H:%M:%S"):
        timeArray = time.localtime(timestamp)
        return time.strftime(str_format, timeArray)

    @staticmethod
    def string_to_timestamp(str_time):
        timeArray = time.strptime(str_time, "%Y-%m-%d %H:%M:%S")
        return int(time.mktime(timeArray))

    @staticmethod
    def str_to_date(strtime):
        y, m, d = time.strptime(strtime, "%Y-%m-%d %H:%M:%S")[0:3]
        return datetime.date(y, m, d)

    @staticmethod
    def str_to_datetime(strtime, str_format="%Y-%m-%d %H:%M:%S"):
        return datetime.datetime.strptime(strtime, str_format)


    @staticmethod
    def timestamp_to_date(timestamp):
        return DateUtil.str_to_date(DateUtil.timestamp_to_string(timestamp))

    @staticmethod
    def str_to_datehour(strtime):
        return datetime.datetime.strptime(strtime, "%Y-%m-%d %H")

    @staticmethod
    def date_to_timestamp(date):
        time_array = time.strptime("%s" % date, "%Y-%m-%d")
        return int(time.mktime(time_array))

    @staticmethod
    def datetime_to_str(dt):
        return dt.strftime("%Y-%m-%d %H:%M:%S")

    @staticmethod
    def datetime_to_date_str(dt):
        a = dt.strftime("%Y-%m-%d %H:%M:%S")
        return a.split(" ")[0]

    @staticmethod
    def datetime_to_hour_minute_str(dt):
        a = dt.strftime("%Y-%m-%d %H:%M:%S")
        return ":".join(a.split(" ")[1].split(":")[0:-1])
