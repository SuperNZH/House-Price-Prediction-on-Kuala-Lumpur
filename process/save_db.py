# -*- coding:utf-8 -*-
import time
import django
import os

os.environ.setdefault("DJANGO_SETTINGS_MODULE", "hpp_system.settings")
django.setup()


from app.models import *
import csv
DataModel.objects.all().delete()

with open('PropertiesAfterPreprocessed.csv', 'r') as f:
    reader = csv.reader(f)
    is_first = True
    for row in reader:
        print(row)
        if not is_first:
            row = row[1:]
            insert_data = {"location": row[0], "car_parks": row[3],
                           "furnishing": row[4], "rooms_num": row[5], "property_type_super_group": row[6],
                           "size_type": row[7]}
            if row[1]:
                insert_data["price"] = row[1]
            if row[2]:
                insert_data["bathrooms"] = row[2]
            if row[8]:
                insert_data["size_num"] = row[8]
            if row[9]:
                insert_data["price_per_area"] = row[9]
            if row[10]:
                insert_data["price_per_room"] = row[10]

            DataModel.objects.create(**insert_data)
        is_first = False






