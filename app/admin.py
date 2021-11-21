from django.contrib import admin

# Register your models here.
from django.contrib import admin
from .models import *


class DataModelAdmin(admin.ModelAdmin):

    list_display = [item.attname for item in DataModel._meta.fields]
# Register your models here
admin.site.register(DataModel, DataModelAdmin)