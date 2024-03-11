const request = require("request");
const polyline = require("polyline");

const MAPQUEST_API_KEY = process.env.MAPQUEST_API_KEY;
const MAPQUEST_API_URL = "http://www.mapquestapi.com/directions/v2/route";

const TOLLGURU_API_KEY = process.env.TOLLGURU_API_KEY;
const TOLLGURU_API_URL = "https://apis.tollguru.com/toll/v2";
const POLYLINE_ENDPOINT = "complete-polyline-from-mapping-service";

// From and To locations (country name required)
const source = "Philadelphia, PA, USA";
const destination = "New York, NY, USA";

// Explore https://tollguru.com/toll-api-docs to get the best of all the parameters that tollguru has to offer
const requestParameters = {
  vehicle: {
    type: "2AxlesAuto",
  },
  // Visit https://en.wikipedia.org/wiki/Unix_time to know the time format
  departure_time: "2021-01-05T09:46:08Z",
};

const url = `${MAPQUEST_API_URL}?key=${MAPQUEST_API_KEY}&from=${source}&to=${destination}&fullShape=true`;

const head = (arr) => arr[0];
const flatten = (arr, x) => arr.concat(x);

// JSON path "$..shapePoints"
const getPoints = (body) =>
  body.route.shape.shapePoints.reduce(
    (arr, x, i, origArr) =>
      i % 2 == 0 ? [...arr, [origArr[i], origArr[i + 1]]] : arr,
    []
  );

const getPolyline = (body) => polyline.encode(getPoints(JSON.parse(body)));

const getRoute = (cb) => request.get(url, cb);

//const handleRoute = (e, r, body) => console.log(getPolyline(body))

//getRoute(handleRoute)
//return;

const tollguruUrl = `${TOLLGURU_API_URL}/${POLYLINE_ENDPOINT}`;

const handleRoute = (e, r, body) => {
  console.log(body);
  const _polyline = getPolyline(body);
  console.log(_polyline);

  request.post(
    {
      url: tollguruUrl,
      headers: {
        "content-type": "application/json",
        "x-api-key": TOLLGURU_API_KEY,
      },
      body: JSON.stringify({
        source: "mapquest",
        polyline: _polyline,
        ...requestParameters,
      }),
    },
    (e, r, body) => {
      console.log(e);
      console.log(body);
    }
  );
};

getRoute(handleRoute);
