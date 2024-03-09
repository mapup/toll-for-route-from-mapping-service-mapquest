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

source = "Philadelphia, PA"
destination = "New York, NY"

# Explore https://tollguru.com/toll-api-docs to get best of all the parameter that TollGuru has to offer
request_parameters = {
    "vehicle": {
        "type": "2AxlesAuto"
    },
    # Visit https://en.wikipedia.org/wiki/Unix_time to know the time format
    "departure_time": "2021-01-05T09:46:08Z",
}

# Fetching polyline from MapQuest
def get_polyline_from_mapquest(source, destination):
    try:
        url = "{a}?key={b}&from={c}&to={d}&fullShape=true".format(
            a=MAPQUEST_API_URL,
            b=MAPQUEST_API_KEY,
            c=source,
            d=destination,
        )
        # Making the request to MapQuest API
        response = requests.get(url)
        response.raise_for_status()  # Raise an exception for HTTP errors

        # Converting the response to JSON
        response_json = response.json()
        
        # Extracting all the coordinates and making lat-lon pair
        coordinate_list = []
        for i in range(0, len(response_json["route"]["shape"]["shapePoints"]), 2):
            coordinate_list.append(
                (
                    response_json["route"]["shape"]["shapePoints"][i],
                    response_json["route"]["shape"]["shapePoints"][i + 1],
                )
            )
        # We will encode these coordinates(lat-lon) using encode function from polyline module to generate polyline
        polyline_from_mapquest = poly.encode(coordinate_list)
        return polyline_from_mapquest
    except requests.RequestException as e:
        print("Error in making the request:", e)
        return None
    except KeyError as e:
        print("Error in parsing the response:", e)
        return None

# Calling Tollguru API
def get_rates_from_tollguru(polyline):
    # TollQuru query URL
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
    # Print(response_tollguru)
    # Checking for errors or printing rates
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
