class WeathersController < ApplicationController
        def current
                feed = "http://api.wunderground.com/auto/wui/geo/ForecastXML/index.xml?query=77004"
                forecastdays = Crack::XML.parse(open(feed))["forecast"]["simpleforecast"]["forecastday"]
                
                current_weather_feed = "http://api.wunderground.com/weatherstation/WXCurrentObXML.asp?ID=KTXHOUST78"
                current_observation = Crack::XML.parse(open(current_weather_feed))["current_observation"]
                
                forecastdays[0]["current"] = { :fahrenheit => current_observation["temp_f"],
                                              :celsius => current_observation["temp_c"] } 
                forecastdays[0]["observation_time"] = current_observation["observation_time"]
                                              
                render :json => forecastdays.to_json
         end
end

