from django.contrib import admin
from .models import *
# Register your models here.


class UserProfileAdmin(admin.AdminSite):
    list_display = ('username', 'email', 'mobile', 'is_superuser', 'date_joined')
    search_fields = ('username', 'email', 'phone',)
    list_filter = ('date_joined',)

admin.site.register(UserProfile)