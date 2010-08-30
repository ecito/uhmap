class ParkingsController < ApplicationController
	def writefile
			File.open('public/parking_capacity.kml.json', 'w') do |outputfile| # 'w' denotes "write mode".
 				 outputfile.puts "Yes" 
		end
		render :text => "Done"   
	end

	def converthash
    
parkings = []
interest = { :interest =>
                      { :name => "name2", 
                        :description => "description2", 
                        :marker_icon => "style2",
                        :picture => nil,
                        :latitude => 24.2324252,
                        :longitude => -92.232323,
                        :created_at => "2009-11-22T20:17:30Z", # FIX
                        :url => nil,
                        :category => "Parking"
                      }
                   }

      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest
      parkings << interest


      render :text => parkings.to_json
      return nil
                   
	end


	def capacity
		#feed = "http://api.wunderground.com/auto/wui/geo/ForecastXML/index.xml?query=77004"
		#feed = "http://maps.google.com/maps/ms?ie=UTF8&hl=en&oe=UTF8&msa=0&msid=102127978412867153719.00047159c9b1f53d544b6&output=kml"
		#render :text => Crack::XML.parse(open(feed,"User-Agent"=>"Mozilla 4.0")).to_json
		render :json => File.open('parking_capacity.kml.json')
	end

	def regenerate
		# Regenerate parking .json
stylemap = {}

feed = "http://maps.google.com/maps/ms?ie=UTF8&hl=en&oe=UTF8&msa=0&msid=102127978412867153719.00047159c9b1f53d544b6&output=kml"
file = open(feed,"User-Agent"=>"Mozilla 4.0")
doc = REXML::Document.new file

root = doc.elements["kml/Document"]
root.elements.each("Style") { |element| 
  style = element.attributes["id"]
  
  href = element.elements["IconStyle/Icon/href"]
  unless href.nil?
    image = File.basename(href[0].to_s)
    stylemap[style] = image
  end
}

placemarks = []


root.elements.each("Placemark") { |element| 
  name = element.elements["name"][0]
  
  if name != "Legend"
    description = element.elements["description"][0]
  
    unless description.nil?
      description = description.to_s.gsub(/<\/?[^>]*>/, "")
      description = description.to_s.gsub(/\n/, "")
    end
    
    style = element.elements["styleUrl"][0].to_s.delete('#')
    
    point = element.elements["Point/coordinates"][0]
    point = point.to_s.split(',')
    long = point[0]
    lat = point[1]
    marker_icon = stylemap[style]

    interest = { :interest =>
                      { :name => name, 
                        :description => description, 
                        :marker_icon => marker_icon,
                        :picture => nil,
                        :latitude => lat,
                        :longitude => long,
                        :created_at => "2009-11-22T20:17:30Z", # FIX
                        :url => nil,
                        :category => "Parking"
                      }
                   }
      
      render :text => interest.to_json
      return nil
                   
      placemarks << interest

  end
}

	File.open('public/parking_capacity.kml.json', 'w') do |outputfile| # 'w' denotes "write mode".
 		 outputfile.puts placemarks.to_json
	end   
	
	return true

	end

end
