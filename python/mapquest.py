#Importing modules
import json
import requests
import polyline as Poly
import os


'''Fetching Polyline from mapquest'''

# Token from Mapquest
key = os.environ.get('MAPQUEST')

#Source
#source = 'Dallas, TX'
source = '1615 Dundas St W, Whitby, ON L1P 1Y9, Canada '
#Destination
#destination = 'New York, NY'
destination ='3730 Concession Rd 8, Orono, ON L0B 1M0, Canada'
#Query Mapquest with Key and Source-Destination 
url = 'http://www.mapquestapi.com/directions/v2/route?key={a}&from={b}&to={c}&fullShape=true'.format(a=key,b=source,c=destination)
 
#converting the response to json
response=requests.get(url).json()
print(response)
#Extracting all the coordinates and making lat-lon pair
coordinate_list=[]
for i in range(0,len(response['route']['shape']['shapePoints']),2):
               coordinate_list.append((response['route']['shape']['shapePoints'][i],response['route']['shape']['shapePoints'][i+1]))



#We will encode these coordinates(lat-lon) using encode function from polyline module to generate polyline
polyline = Poly.encode(coordinate_list)
print(coordinate_list)
#checking for errors in response      #'''TODO check for errors in bingmap response


'''Calling Tollguru API'''

#API key for Tollguru
Tolls_Key= os.environ.get('TOLLGURU')

#Tollguru querry url
Tolls_URL = 'https://dev.tollguru.com/v1/calc/route'

#Tollguru resquest parameters
headers = {
            'Content-type': 'application/json',
            'x-api-key': Tolls_Key
          }
params = {
            'source': "mapquest",
            'polyline': polyline ,                      #  this is polyline that we fetched from the mapping service     
            'vehicleType': '2AxlesAuto',                #'''TODO - Need to provide users a slist of acceptable values for vehicle type'''
            'departure_time' : "2021-01-05T09:46:08Z"   #'''TODO - Specify time formats'''
        }

#Requesting Tollguru with parameters
response_tollguru= requests.post(Tolls_URL, json=params, headers=headers).json()

#checking for errors or printing rates
if str(response_tollguru).find('message')==-1:
    print('\n The Rates Are ')
    #extracting rates from Tollguru response is no error
    #print(*response_tollguru['summary']['rates'].items(),end="\n\n")
    print(*response_tollguru['route']['costs'].items(),end="\n\n")
else:
    raise Exception(response_tollguru['message'])

