# !!!! 测Price推荐用ExtraTree， 测Price per area推荐用DecisionTree


# functions for train and test
def LRModel(model, X_train = X_train, y_train = y_train, X_test = input_param):
    model.fit(X_train, y_train)
    # 这个X_test就是咱们之前的那个input_param
    predictions = model.predict(X_test)
    return predictions

# ExtraTreeRegressor()
# 训练price
predictions = LRModel(model = ExtraTreeRegressor(), X_train=X_train, y_train=y_train["Price"], X_test=X_test)
# 训练Price per area
predictions = LRModel(model = ExtraTreeRegressor(), X_train=X_train, y_train=y_train["Price per Area"], X_test=X_test)


# DecisionTreeRegressor()
# 训练price
predictions = LRModel(model = DecisionTreeRegressor(),X_train=X_train, y_train=y_train["Price"],X_test=X_test)
# 训练price per area
predictions = LRModel(model = DecisionTreeRegressor(),X_train=X_train, y_train=y_train["Price per Area"],X_test=X_test)


