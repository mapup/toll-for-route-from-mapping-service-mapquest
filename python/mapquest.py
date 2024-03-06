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

source = "Dallas, TX"
destination = "New York, NY"

"""Fetching Polyline from mapquest"""


def get_polyline_from_mapquest(source, destination):
    url = "{a}?key={b}&from={c}&to={d}&fullShape=true".format(
        a=MAPQUEST_API_URL,
        b=MAPQUEST_API_KEY,
        c=source,
        d=destination,
    )
    # converting the response to json
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
    # We will encode these coordinates(lat-lon) using encode function from polyline module to generate polyline
    polyline_from_mapquest = poly.encode(coordinate_list)
    return polyline_from_mapquest


"""Calling Tollguru API"""


def get_rates_from_tollguru(polyline):
    # Tollguru querry url
    Tolls_URL = f"{TOLLGURU_API_URL}/{POLYLINE_ENDPOINT}"
    # Tollguru resquest parameters
    headers = {"Content-type": "application/json", "x-api-key": TOLLGURU_API_KEY}
    params = {
        # Explore https://tollguru.com/developers/docs/ to get best of all the parameter that tollguru has to offer
        "source": "mapquest",
        "polyline": polyline,  # this is the encoded polyline that we made
        "vehicleType": "2AxlesAuto",  #'''Visit https://tollguru.com/developers/docs/#vehicle-types to know more options'''
        "departure_time": "2021-01-05T09:46:08Z",  #'''Visit https://en.wikipedia.org/wiki/Unix_time to know the time format'''
    }
    # Requesting Tollguru with parameters
    response_tollguru = requests.post(
        Tolls_URL, json=params, headers=headers, timeout=200
    ).json()
    # print(response_tollguru)
    # checking for errors or printing rates
    if str(response_tollguru).find("message") == -1:
        return response_tollguru["route"]["costs"]
    else:
        raise Exception(response_tollguru["message"])


"""Program Starts"""
# Step 1 : Get Polyline from Mapquest
polyline_from_mapquest = get_polyline_from_mapquest(source, destination)

# Step 2 : Get rates from Tollguru
rates_from_tollguru = get_rates_from_tollguru(polyline_from_mapquest)

# Step 3 : Print the rates of all the available modes of payment
if rates_from_tollguru == {}:
    print("The route doesn't have tolls")
else:
    print(f"The rates are \n {rates_from_tollguru}")

"""Program Ends"""
