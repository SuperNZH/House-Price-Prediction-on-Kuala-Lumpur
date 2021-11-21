import pandas as pd

import sklearn.feature_selection
from sklearn.preprocessing import StandardScaler
import warnings
# filter warnings
warnings.filterwarnings('ignore')
# 正常显示中文
from pylab import mpl
mpl.rcParams['font.sans-serif'] = ['SimHei']
# 正常显示符号
from matplotlib import rcParams
rcParams['axes.unicode_minus']=False

X_train = None
y_train = None
X_test = None
y_test = None

def init():
    global X_train, y_train, X_test
    properties = pd.read_csv('PropertiesAfterPreprocessed_For_ModelTraining.csv')
    Xy = properties.loc[properties["Size Type"] == "Built-up"]

    Xy = Xy.loc[:, [
                       "Location", "Bathrooms", "Car Parks", "Furnishing",
                       "Rooms Num", "Property Type Supergroup", "Size Num",
                       "Price", "Price per Area", "Price per Room"]]

    Xy.loc[:, "Car Parks"] = Xy["Car Parks"].fillna(0)

    Xy = Xy.loc[Xy.isna().sum(axis=1) == 0]

    Xy = Xy.loc[Xy["Furnishing"] != "Unknown"]

    Xy = pd.get_dummies(Xy)
    Xy["Size Num"].sort_values()
    Xy["Size Num"].sort_values(ascending=False)
    Xy = Xy.loc[Xy["Size Num"].between(250, 20000)]
    selectors = []
    for feature in ["Bathrooms", "Car Parks", "Rooms Num"]:
        selectors.append(Xy[feature].between(
            Xy[feature].quantile(0.001),
            Xy[feature].quantile(0.999)))

    Xy = Xy.loc[(~pd.DataFrame(selectors).T).sum(axis=1) == 0]
    Xy, Xy_feature_selection = sklearn.model_selection.train_test_split(
        Xy, test_size=0.25, random_state=101)
    cols = ["Bathrooms", "Car Parks", "Rooms Num", "Size Num"]
    Xy_feature_selection[cols] = sklearn.preprocessing.MinMaxScaler().fit_transform(
        Xy_feature_selection[cols])
    Xy[cols] = sklearn.preprocessing.MinMaxScaler().fit_transform(Xy[cols])
    Xy = Xy.drop(["Bathrooms", "Rooms Num"], axis=1)
    Xy_feature_selection = Xy_feature_selection.drop(["Bathrooms", "Rooms Num"], axis=1)
    Xy = Xy.drop("Price per Room", axis=1)
    Xy_feature_selection = Xy_feature_selection.drop("Price per Room", axis=1)

    Xy_train, Xy_test = sklearn.model_selection.train_test_split(Xy, test_size=0.2, random_state=101)
    X_train = Xy_train.drop(["Price", "Price per Area"], axis=1)
    y_train = Xy_train[["Price", "Price per Area"]]
    X_test = Xy_test.drop(["Price", "Price per Area"], axis=1)
    y_test = Xy_test[["Price", "Price per Area"]]


### 接口方式
def get_result(input_param, model_choice, price_choice):
    """【】
    :param input_param: [[3,2,0,0,1,0,0,0,0]]   前端：carnum sizenum  location  Furnishing  PropertyType
    :return:
    """
    global X_train, y_train

    from sklearn.tree import DecisionTreeRegressor
    from sklearn.tree import ExtraTreeRegressor

    if model_choice == 0:
        model = DecisionTreeRegressor()
        model.fit(X_train, y_train)
        y_pred = model.predict(input_param)
        if price_choice == 0:
            return y_pred[0][0]
        else:
            return y_pred[0][1]

    if model_choice == 1:
        model = ExtraTreeRegressor()
        model.fit(X_train, y_train)
        y_pred = model.predict(input_param)
        if price_choice == 0:
            return y_pred[0][0]
        else:
            return y_pred[0][1]



if __name__ == '__main__':
    init()
    columns = ['Car Parks', 'Size Num', 'Location_ampang', 'Location_ampang hilir',
       'Location_bandar damai perdana', 'Location_bandar menjalara',
       'Location_bangsar', 'Location_bangsar south', 'Location_batu caves',
       'Location_brickfields', 'Location_bukit bintang',
       'Location_bukit jalil', 'Location_bukit tunku (kenny hills)',
       'Location_cheras', 'Location_city centre',
       'Location_country heights damansara', 'Location_damansara heights',
       'Location_desa pandan', 'Location_desa parkcity',
       'Location_desa petaling', 'Location_dutamas', 'Location_jalan ipoh',
       'Location_jalan klang lama (old klang road)', 'Location_jalan kuching',
       'Location_jalan sultan ismail', 'Location_kepong', 'Location_keramat',
       'Location_kl city', 'Location_kl eco city', 'Location_kl sentral',
       'Location_klcc', 'Location_kuchai lama', 'Location_mont kiara',
       'Location_oug', 'Location_pandan perdana', 'Location_pantai',
       'Location_salak selatan', 'Location_segambut', 'Location_sentul',
       'Location_seputeh', 'Location_setapak', 'Location_setiawangsa',
       'Location_sri hartamas', 'Location_sri petaling',
       'Location_sungai besi', 'Location_sunway spk', 'Location_taman desa',
       'Location_taman melawati', 'Location_taman tun dr ismail',
       'Location_titiwangsa', 'Location_wangsa maju',
       'Furnishing_Fully Furnished', 'Furnishing_Partly Furnished',
       'Furnishing_Unfurnished', 'Property Type Supergroup_Apartment',
       'Property Type Supergroup_Bungalow',
       'Property Type Supergroup_Condominium', 'Property Type Supergroup_Flat',
       'Property Type Supergroup_Residential Land',
       'Property Type Supergroup_Semi-detached House',
       'Property Type Supergroup_Serviced Residence',
       'Property Type Supergroup_Terrace/Link House',
       'Property Type Supergroup_Townhouse']

    data_location = [0] * len([item for item in columns if item.find("Location") !=-1])
    data_location[0] = 1
    data_furnishing = [0] * len([item for item in columns if item.find("Furnishing") !=-1])
    data_furnishing[0] = 1
    data_property_type = [0] * len([item for item in columns if item.find("Property") !=-1])
    data_property_type[0] = 1

    data = [1, 2000]
    data.extend(data_location)
    data.extend(data_furnishing)
    data.extend(data_property_type)
    print(data)
    df = pd.DataFrame(columns=columns, data=[data])
    print(df)

    print(get_result(df, 0, 1))

