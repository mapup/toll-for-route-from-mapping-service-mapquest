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
* You are also create additional keys, as per you requirements


With this in place, make a GET request: http://www.mapquestapi.com/directions/v2/route?key=${key}&from=${source}&to=${destination}&fullShape=true

### Note:
* Mapquest doesn't send us route geometry as `polyline`, instead it
  sends an array containing coordinates. We can encode it as `polyline`
* We are sending `fullShape` as `true` so that we get a detailed
  geometry instead of an aproximation.

```ruby
# Using each slice enum we convert flat array to arrays of coordinate pairs
coordinate_pairs = json_parsed['route']['shape']['shapePoints'].each_slice(2).to_a
google_encoded_polyline = FastPolylines.encode(coordinate_pairs)
```

```ruby
require 'HTTParty'
require 'json'
require "fast_polylines"

# Source Details 
SOURCE = 'Dallas, TX'
# Destination Details
DESTINATION = 'New York, NY'

# GET Request to MapQuest for Polyline
KEY = ENV["MAPQUEST_KEY"]
MAPQUEST_URL = "http://www.mapquestapi.com/directions/v2/route?key=#{KEY}&from=#{SOURCE}&to=#{DESTINATION}&fullShape=true"
RESPONSE = HTTParty.get(MAPQUEST_URL).body
json_parsed = JSON.parse(RESPONSE)

# Extracting coordinate pairs from JSON and encoded to google polyline
coordinate_pairs = json_parsed['route']['shape']['shapePoints'].each_slice(2).to_a
google_encoded_polyline = FastPolylines.encode(coordinate_pairs)
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

```ruby
# Sending POST request to TollGuru
TOLLGURU_URL = 'https://dev.tollguru.com/v1/calc/route'
TOLLGURU_KEY = ENV['TOLLGURU_KEY']
headers = {'content-type' => 'application/json', 'x-api-key' => TOLLGURU_KEY}
body = {'source' => "mapbox", 'polyline' => google_encoded_polyline, 'vehicleType' => "2AxlesAuto", 'departure_time' => "2021-01-05T09:46:08Z"}
tollguru_response = HTTParty.post(TOLLGURU_URL,:body => body.to_json, :headers => headers)
```

The working code can be found in index.js file.

## License
ISC License (ISC). Copyright 2020 &copy;TollGuru. https://tollguru.com/

Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby granted, provided that the above copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
