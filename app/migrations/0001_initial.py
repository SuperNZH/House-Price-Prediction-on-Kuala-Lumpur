# Generated by Django 2.1.7 on 2021-06-06 18:24

from django.db import migrations, models


class Migration(migrations.Migration):

    initial = True

    dependencies = [
    ]

    operations = [
        migrations.CreateModel(
            name='DataModel',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('location', models.CharField(max_length=100, verbose_name='location')),
                ('price', models.IntegerField(default=None, null=True, verbose_name='price')),
                ('bathrooms', models.IntegerField(default=None, null=True, verbose_name='bathrooms')),
                ('car_parks', models.CharField(default='', max_length=100, verbose_name='car_parks')),
                ('furnishing', models.CharField(max_length=100, verbose_name='furnishing')),
                ('rooms_num', models.CharField(default='', max_length=100, verbose_name='rooms_num')),
                ('property_type_super_group', models.CharField(max_length=100, verbose_name='property_type_super_group')),
                ('size_type', models.CharField(max_length=100, verbose_name='size_type')),
                ('size_num', models.IntegerField(default=None, null=True, verbose_name='size_num')),
                ('price_per_area', models.CharField(max_length=500, verbose_name='price_per_area')),
                ('price_per_room', models.FloatField(default=None, null=True, verbose_name='price_per_room')),
            ],
            options={
                'verbose_name': '数据',
                'verbose_name_plural': '数据',
                'db_table': 'data',
            },
        ),
    ]
