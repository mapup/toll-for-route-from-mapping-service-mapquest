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

```javascript

// JSON path "$..shapePoints"
const getPoints = body => body.route.shape.shapePoints
  .reduce((arr, x, i, origArr) => i % 2 == 0 ? [...arr, [origArr[i], origArr[i+1]]] : arr, [])
```

```javascript
const request = require("request");
const polyline = require("polyline");

// REST API key from Mapquest
const key = process.env.MAPQUEST_KEY
const tollguruKey = process.env.TOLLGURU_KEY;

const source = 'Dallas, TX'

const destination = 'New York, NY';

const url = `http://www.mapquestapi.com/directions/v2/route?key=${key}&from=${source}&to=${destination}&fullShape=true`;


const head = arr => arr[0];
const flatten = (arr, x) => arr.concat(x);

// JSON path "$..shapePoints"
const getPoints = body => body.route.shape.shapePoints
  .reduce((arr, x, i, origArr) => i % 2 == 0 ? [...arr, [origArr[i], origArr[i+1]]] : arr, [])

const getPolyline = body => polyline.encode(getPoints(JSON.parse(body)));

const getRoute = (cb) => request.get(url, cb);

const handleRoute = (e, r, body) => console.log(getPolyline(body))

getRoute(handleRoute)
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

```javascript

const tollguruUrl = 'https://dev.tollguru.com/v1/calc/route';

const handleRoute = (e, r, body) =>  {

  console.log(body);
  const _polyline = getPolyline(body);
  console.log(_polyline);

  request.post(
    {
      url: tollguruUrl,
      headers: {
        'content-type': 'application/json',
        'x-api-key': tollguruKey
      },
      body: JSON.stringify({
        source: "mapquest",
        polyline: _polyline,
        vehicleType: "2AxlesAuto",
        departure_time: "2021-01-05T09:46:08Z"
      })
    },
    (e, r, body) => {
      console.log(e);
      console.log(body)
    }
  )
}

getRoute(handleRoute);
```

The working code can be found in index.js file.

## License
ISC License (ISC). Copyright 2020 &copy;TollGuru. https://tollguru.com/

Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby granted, provided that the above copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
