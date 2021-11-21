import pandas as pd
import os
import sklearn.feature_selection
from sklearn.preprocessing import StandardScaler
import warnings
# filter warnings
warnings.filterwarnings('ignore')


X_train = None
y_train = None
X_test = None
y_test = None
Test_Xy = None

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

def init():
    global X_train, y_train, X_test, Test_Xy
    property_file_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'PropertiesAfterPreprocessed_For_ModelTraining.csv')
    properties = pd.read_csv(property_file_path)
    Xy = properties.loc[properties["Size Type"] == "Built-up"]

    Xy = Xy.loc[:, ["Location", "Bathrooms", "Car Parks", "Furnishing",
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
    Test_Xy = Xy
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


def LRModel(model, a_train, b_train, _test):
    model.fit(a_train, b_train)
    # 这个X_test就是咱们之前的那个input_param
    predictions = model.predict(_test)
    return predictions

# 模型边界0 - 7
def getMinMaxCarParks(carNum):
    global Test_Xy

    # up = carNum - Test_Xy["Car Parks"].min()
    # down = Test_Xy["Car Parks"].unique().std()
    # res = up / down
    # return res

    up = carNum - Test_Xy["Car Parks"].min()
    down = Test_Xy["Car Parks"].max() - Test_Xy["Car Parks"].min()
    res = up / down

    if res > 0:
        return res
    else:
        return -res


# 模型边界不是11 - 820000， 应该是250-19180
def getMinMaxSizeNum(sizeNum):
    global Test_Xy

    up = sizeNum - 250
    down = 19180 - 250
    res = up/down


    # up = sizeNum - 1617.3873
    # down = 10500.8877
    # res = up/down

    # up = sizeNum - Test_Xy["Size Num"].mean()
    # down = Test_Xy["Size Num"].max() - Test_Xy["Size Num"].min()
    # res = up / down

    # up = sizeNum - Test_Xy["Size Num"].mean()
    # down = Test_Xy["Size Num"].unique().std()
    # res = up / down


    if res > 0:
        return res
    else:
        return -res

### 接口方式
def get_result(car_parks, size_num, location_choice, furnishing_choice, property_type_super_group_choice, model_choice, price_choice):
    """【】
    :param input_param: [[3,2,0,0,1,0,0,0,0]]   前端：carnum sizenum  location  Furnishing  PropertyType
    :return:
    """
    car_parks = getMinMaxCarParks(car_parks)
    size_num = getMinMaxSizeNum(size_num)
    input_param = [car_parks, size_num]
    data_location = [0] * len([item for item in columns if item.find("Location_") !=-1])
    data_location[location_choice] = 1
    data_furnishing = [0] * len([item for item in columns if item.find("Furnishing_") !=-1])
    data_furnishing[furnishing_choice] = 1
    data_property_type = [0] * len([item for item in columns if item.find("Property Type Supergroup_") !=-1])
    data_property_type[property_type_super_group_choice] = 1

    input_param.extend(data_location)
    input_param.extend(data_furnishing)
    input_param.extend(data_property_type)
    print(input_param)
    input_param_df = pd.DataFrame(columns=columns, data=[input_param])
    print(input_param_df)

    global X_train, y_train

    from sklearn.tree import DecisionTreeRegressor
    from sklearn.tree import ExtraTreeRegressor

    if model_choice == 0:
        model = DecisionTreeRegressor()
    else:
        model = ExtraTreeRegressor()

    if price_choice == 0:
        return LRModel(model, X_train, y_train["Price"], input_param_df)
    else:
        return LRModel(model, X_train, y_train["Price per Area"], input_param_df)


init()

if __name__ == '__main__':
    print(get_result(1, 904, 41, 1, 0, 1, 0))




