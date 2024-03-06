require 'HTTParty'
require 'json'
require "fast_polylines"
require "cgi"

MAPQUEST_API_KEY = os.environ.get("MAPQUEST_API_KEY")
MAPQUEST_API_URL = "http://www.mapquestapi.com/directions/v2/route"

TOLLGURU_API_KEY = os.environ.get("TOLLGURU_API_KEY")
TOLLGURU_API_URL = "https://apis.tollguru.com/toll/v2"
POLYLINE_ENDPOINT = "complete-polyline-from-mapping-service"

def get_toll_rate(from,to)
    # Source Details 
    source = from
    # Destination Details
    destination = to

    mapquest_url = "#{MAPQUEST_API_URL}?key=#{MAPQUEST_API_KEY}&from=#{CGI::escape(source)}&to=#{CGI::escape(destination)}&fullShape=true"
    response = HTTParty.get(mapquest_url).body
    json_parsed = JSON.parse(response)

    # Extracting coordinate pairs from JSON and encoded to google polyline
    coordinate_pairs = json_parsed['route']['shape']['shapePoints'].each_slice(2).to_a
    google_encoded_polyline = FastPolylines.encode(coordinate_pairs)

    # Sending POST request to TollGuru
    TOLLGURU_URL = "#{TOLLGURU_API_URL}/#{POLYLINE_ENDPOINT}" 
    headers = {'content-type' => 'application/json', 'x-api-key' => TOLLGURU_API_KEY}
    body = {'source' => "mapbox", 'polyline' => google_encoded_polyline, 'vehicleType' => "2AxlesAuto", 'departure_time' => "2021-01-05T09:46:08Z"}
    tollguru_response = HTTParty.post(tollguru_url,:body => body.to_json, :headers => headers)
    begin
        toll_body = JSON.parse(tollguru_response.body)    
        if toll_body["route"]["hasTolls"] == true
            return google_encoded_polyline,toll_body["route"]["costs"]["tag"], toll_body["route"]["costs"]["cash"] 
        else
            raise "No tolls encountered in this route"
        end
    rescue Exception => e
        puts e.message 
    end

end
