desc "Update parking from UH police"
task :update_parking do
  ENV["RAILS_ENV"] = 'production'
  RAILS_ENV.replace('production') if defined?(RAILS_ENV)

  load "#{RAILS_ROOT}/config/environment.rb"

	puts "updating parking info from UH"
	
	parking_db = Parking.find_or_create_by_id(1)
  parking_db.text = get_parking
  parking_db.save
end

def get_parking
  require 'rexml/document'
  require 'open-uri'
  
  stylemap = {}

  response = ''
  open("http://maps.google.com/maps/ms?ie=UTF8&hl=en&oe=UTF8&msa=0&msid=102127978412867153719.00047159c9b1f53d544b6&output=kml", "User-Agent" => "Mozilla 4.0") { |f| response = f.read }

  doc = REXML::Document.new(response)
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

  parking_category = Category.find_by_name("Parking")
  parking_category.interests = []
  
  root.elements.each("Placemark") do |element| 
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
        
        unless description.to_s == "DO NOT DELETE"
          
          interest = Interest.find_or_create_by_name(name.to_s)
          interest.update_attributes({
            :name => name.to_s, 
            :description => description.to_s, 
            :marker_icon => stylemap[style].to_s,
            :latitude => lat.to_f,
            :longitude => long.to_f
          })
          
          parking_category.interests << interest
  
        end
      end
    end
  end

end