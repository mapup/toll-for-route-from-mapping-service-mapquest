# [MapQuest](https://developer.mapquest.com/)

### Get key to access MapQuest (if you have an API key skip this)
#### Step 1: Signup/Login
* Create an account to access [MapQuest Developer Network](https://developer.mapquest.com/)
* go to signup/login link https://developer.mapquest.com/user/login
* you will need to agree to MapQuest's Terms of Service https://hello.mapquest.com/terms-of-use

#### Step 2: Getting api key
* Login to Mapquest Developer Network
* Go to https://developer.mapquest.com/user/me/apps
* You will be presented with a default key
* You are also create additional keys, as per you requirements.


With this in place, make a GET request: http://www.mapquestapi.com/directions/v2/route?key=${key}&from=${source}&to=${destination}&fullShape=true

### Note:
* Mapquest doesn't send us route geometry as `polyline`, instead it
  sends an array containing coordinates. We can encode it as `polyline`
* We are sending `fullShape` as `true` so that we get a detailed
  geometry instead of an aproximation.

```python
import json
import requests
import polyline as poly
import os

#Token from Mapquest
key = os.environ.get('Mapquest_API_Key')

def get_polyline_from_mapquest(source, destination):
    url = 'http://www.mapquestapi.com/directions/v2/route?key={a}&from={b}&to={c}&fullShape=true'.format(a=key,b=source,c=destination)
    #converting the response to json
    response=requests.get(url).json()
    #Extracting all the coordinates and making lat-lon pair
    coordinate_list=[]
    for i in range(0,len(response['route']['shape']['shapePoints']),2):
                   coordinate_list.append((response['route']['shape']['shapePoints'][i],response['route']['shape']['shapePoints'][i+1]))
    #We will encode these coordinates(lat-lon) using encode function from polyline module to generate polyline
    polyline_from_mapquest= poly.encode(coordinate_list)
    return(polyline_from_mapquest)
```

Note:

We extracted the polyline for a route from MapQuest API

We need to send this route polyline to TollGuru API to receive toll information

## [TollGuru API](https://tollguru.com/developers/docs/)

### Get key to access TollGuru polyline API
* create a dev account to receive a free key from TollGuru https://tollguru.com/developers/get-api-key
* suggest adding `vehicleType` parameter. Tolls for cars are different than trucks and therefore if `vehicleType` is not specified, may not receive accurate tolls. For example, tolls are generally higher for trucks than cars. If `vehicleType` is not specified, by default tolls are returned for 2-axle cars. 
* Similarly, `departure_time` is important for locations where tolls change based on time-of-the-day.

the last line can be changed to following

```python
import json
import requests
import polyline as poly
import os

#API key for Tollguru
Tolls_Key = os.environ.get('Tollguru_API_New')

def get_rates_from_tollguru(polyline):
    #Tollguru querry url
    Tolls_URL = 'https://dev.tollguru.com/v1/calc/route'
    #Tollguru resquest parameters
    headers = {
                'Content-type': 'application/json',
                'x-api-key': Tolls_Key
                }
    params = {
                #Explore https://tollguru.com/developers/docs/ to get best of all the parameter that tollguru has to offer 
                'source': "mapquest",
                'polyline': polyline,                      # this is the encoded polyline that we made     
                'vehicleType': '2AxlesAuto',                #'''Visit https://tollguru.com/developers/docs/#vehicle-types to know more options'''
                'departure_time' : "2021-01-05T09:46:08Z"   #'''Visit https://en.wikipedia.org/wiki/Unix_time to know the time format'''
                }
    #Requesting Tollguru with parameters
    response_tollguru= requests.post(Tolls_URL, json=params, headers=headers,timeout=200).json()
    #print(response_tollguru)
    #checking for errors or printing rates
    if str(response_tollguru).find('message')==-1:
        return(response_tollguru['route']['costs'])
    else:
        raise Exception(response_tollguru['message'])
```

The working code can be found in mapquest.py file.

## License
ISC License (ISC). Copyright 2020 &copy;TollGuru. https://tollguru.com/

Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby granted, provided that the above copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
