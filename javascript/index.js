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
const getPoints = body => body.route.shape.shapePoints.reduce((arr, x, i, origArr) => i % 2 == 0 ? [...arr, [origArr[i], origArr[i+1]]] : arr, [])

const getPolyline = body => polyline.encode(getPoints(JSON.parse(body)));

const getRoute = (cb) => request.get(url, cb);

//const handleRoute = (e, r, body) => console.log(getPolyline(body))

//getRoute(handleRoute)
//return;

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
      body: JSON.stringify({ source: "mapquest", polyline: _polyline })
    },
    (e, r, body) => {
      console.log(e);
      console.log(body)
    }
  )
}

getRoute(handleRoute);
