# Parse UH Parking .KML Output
#
# UH CampusGuide 2009
require "rubygems"
require "rexml/document"
require "json"


def get_parking
`curl -A "Mozilla 4.0" -L "http://maps.google.com/maps/ms?ie=UTF8&hl=en&oe=UTF8&msa=0&msid=102127978412867153719.00047159c9b1f53d544b6&output=kml" > parking_map.kml`

stylemap = {}

doc = REXML::Document.new File.new("parking_map.kml")
root = doc.elements["kml/Document"]
root.elements.each("Style") { |element| 
  style = element.attributes["id"]
  
  href = element.elements["IconStyle/Icon/href"]
  unless href.nil?
    image = File.basename(href[0].to_s)
    stylemap[style] = image
  end
}

placemarks = Array.new

root.elements.each("Placemark") { |element| 
  name = element.elements["name"][0]
  
  if name != "Legend"
    description = element.elements["description"][0]
    unless description.nil?
      description = description.to_s.gsub(/<\/?[^>]*>/, "")
      description = description.to_s.gsub(/\n/, "")
    end
    
    style = element.elements["styleUrl"][0].to_s.delete('#')
    unless element.elements["Point/coordinates"].nil?
   	 point = element.elements["Point/coordinates"][0]
   	 point = point.to_s.split(',')
   	 long = point[0]
   	 lat = point[1]

    	placemarks << { :interest =>
                      { :name => name, 
                        :description => description, 
                        :marker_icon => stylemap[style],
                        :picture => nil,
                        :latitude => lat,
                        :longitude => long,
                        :created_at => "2009-11-22T20:17:30Z", # FIX
                        :url => nil,
                        :category => "Parking"
                      }
                   }
    end
  end
}


output = placemarks
output.to_json
end

	puts get_parking

