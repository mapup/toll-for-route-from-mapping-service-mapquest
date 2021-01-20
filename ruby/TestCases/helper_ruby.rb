require 'HTTParty'
require 'json'
require "fast_polylines"
require "cgi"

def get_toll_rate(from,to)
    # Source Details 
    source = from
    # Destination Details
    destination = to

    # GET Request to MapQuest for Polyline
    key = ENV["MAPQUEST_KEY"]
    mapquest_url = "http://www.mapquestapi.com/directions/v2/route?key=#{key}&from=#{CGI::escape(source)}&to=#{CGI::escape(destination)}&fullShape=true"
    response = HTTParty.get(mapquest_url).body
    json_parsed = JSON.parse(response)

    # Extracting coordinate pairs from JSON and encoded to google polyline
    coordinate_pairs = json_parsed['route']['shape']['shapePoints'].each_slice(2).to_a
    google_encoded_polyline = FastPolylines.encode(coordinate_pairs)

    # Sending POST request to TollGuru
    tollguru_url = 'https://dev.tollguru.com/v1/calc/route'
    tollguru_key = ENV['TOLLGURU_KEY']
    headers = {'content-type' => 'application/json', 'x-api-key' => tollguru_key}
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