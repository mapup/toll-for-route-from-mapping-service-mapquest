require 'HTTParty'
require 'json'
require "fast_polylines"
require "cgi"

# Source Details 
SOURCE = 'Dallas, TX'
# Destination Details
DESTINATION = 'New York, NY'

# GET Request to MapQuest for Polyline
KEY = ENV["MAPQUEST_KEY"]
MAPQUEST_URL = "http://www.mapquestapi.com/directions/v2/route?key=#{KEY}&from=#{CGI::escape(SOURCE)}&to=#{CGI::escape(DESTINATION)}&fullShape=true"
RESPONSE = HTTParty.get(MAPQUEST_URL).body
json_parsed = JSON.parse(RESPONSE)

# Extracting coordinate pairs from JSON and encoded to google polyline
coordinate_pairs = json_parsed['route']['shape']['shapePoints'].each_slice(2).to_a
google_encoded_polyline = FastPolylines.encode(coordinate_pairs)

# Sending POST request to TollGuru
TOLLGURU_URL = 'https://dev.tollguru.com/v1/calc/route'
TOLLGURU_KEY = ENV['TOLLGURU_KEY']
headers = {'content-type' => 'application/json', 'x-api-key' => TOLLGURU_KEY}
body = {'source' => "mapbox", 'polyline' => google_encoded_polyline, 'vehicleType' => "2AxlesAuto", 'departure_time' => "2021-01-05T09:46:08Z"}
tollguru_response = HTTParty.post(TOLLGURU_URL,:body => body.to_json, :headers => headers)