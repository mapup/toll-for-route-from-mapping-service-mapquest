# Importing modules
import json
import requests
import polyline as poly
import os

MAPQUEST_API_KEY = os.environ.get("MAPQUEST_API_KEY")
MAPQUEST_API_URL = "http://www.mapquestapi.com/directions/v2/route"

TOLLGURU_API_KEY = os.environ.get("TOLLGURU_API_KEY")
TOLLGURU_API_URL = "https://apis.tollguru.com/toll/v2"
POLYLINE_ENDPOINT = "complete-polyline-from-mapping-service"

# Explore https://tollguru.com/toll-api-docs to get best of all the parameter that TollGuru has to offer
request_parameters = {
    "vehicle": {
        "type": "2AxlesAuto"
    },
    # Visit https://en.wikipedia.org/wiki/Unix_time to know the time format
    "departure_time": "2021-01-05T09:46:08Z",
}

# Fetching Polyline from mapquest
def get_polyline_from_mapquest(source, destination):
    url = "{a}?key={b}&from={c}&to={d}&fullShape=true".format(
        a=MAPQUEST_API_URL,
        b=MAPQUEST_API_KEY,
        c=source,
        d=destination,
    )
    # Converting the response to JSON
    response = requests.get(url).json()
    # Extracting all the coordinates and making lat-lon pair
    coordinate_list = []
    for i in range(0, len(response["route"]["shape"]["shapePoints"]), 2):
        coordinate_list.append(
            (
                response["route"]["shape"]["shapePoints"][i],
                response["route"]["shape"]["shapePoints"][i + 1],
            )
        )
    # We will encode these coordinates (lat-lon) using encode function from polyline module to generate polyline
    polyline_from_mapquest = poly.encode(coordinate_list)
    return polyline_from_mapquest


# Calling TollGuru API
def get_rates_from_tollguru(polyline):
    # TollGuru query URL
    Tolls_URL = f"{TOLLGURU_API_URL}/{POLYLINE_ENDPOINT}"
    # TollGuru request parameters
    headers = {"Content-type": "application/json", "x-api-key": TOLLGURU_API_KEY}
    params = {
        # Explore https://tollguru.com/developers/docs/ to get best of all the parameter that TollGuru has to offer
        "source": "mapquest",
        "polyline": polyline,  # This is the encoded polyline that we made
        **request_parameters,
    }
    # Requesting TollGuru with parameters
    response_tollguru = requests.post(
        Tolls_URL, json=params, headers=headers, timeout=200
    ).json()
    # print(response_tollguru)
    # checking for errors or printing rates
    if str(response_tollguru).find("message") == -1:
        return response_tollguru["route"]["costs"]
    else:
        raise Exception(response_tollguru["message"])


"""Testing"""
# Importing Functions
from csv import reader, writer
import time

temp_list = []
with open("testCases.csv", "r") as f:
    csv_reader = reader(f)
    for count, i in enumerate(csv_reader):
        # if count>2:
        #   break
        if count == 0:
            i.extend(
                (
                    "Input_polyline",
                    "Tollguru_Tag_Cost",
                    "Tollguru_Cash_Cost",
                    "Tollguru_QueryTime_In_Sec",
                )
            )
        else:
            try:
                polyline = get_polyline_from_mapquest(i[1], i[2])
                i.append(polyline)
            except:
                i.append("Routing Error")

            start = time.time()
            try:
                rates = get_rates_from_tollguru(polyline)
            except:
                i.append(False)
            time_taken = time.time() - start
            if rates == {}:
                i.append((None, None))
            else:
                try:
                    tag = rates["tag"]
                except:
                    tag = None
                try:
                    cash = rates["cash"]
                except:
                    cash = None
                i.extend((tag, cash))
            i.append(time_taken)
        # print(f"{len(i)}   {i}\n")
        temp_list.append(i)

with open("testCases_result.csv", "w") as f:
    writer(f).writerows(temp_list)

"""Testing Ends"""
