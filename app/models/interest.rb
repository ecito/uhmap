class Interest < ActiveRecord::Base
	belongs_to :category
	
	def self.find_all_by_category name
		Interest.find_all_by_category_id(Category.find_by_name(name))
	end

	def category_name
		self.category.name

	end

	def marker_icon
		unless read_attribute(:marker_icon).empty?
			read_attribute(:marker_icon)
		else
			self.category.default_marker
		end
	end

  def self.tweets
    url = "http://search.twitter.com/search.json?geocode=29.72072515%2C-95.34287452%2C21km&rpp=100"
    
    tweets = []
    JSON.parse(open(url).read)['results'].each do |tweet|
    	
  	  tweets << { :name => "@#{tweet['from_user']}", 
           :description => CGI.unescapeHTML(tweet["text"]), 
           :marker_icon => "marker_info.png",
           :picture => "nil",
           :latitude => 0,
           :longitude => 0,
           :created_at => "2009-11-22T20:17:30Z",
           :url => "http://twitter.com/#{tweet['from_user']}/status/#{tweet['id']}",
           :category_name => "Twitter"
         }
     
  	end
    
    tweets
  end
  
  def self.stops
    stops = Array.new
    Stop.all.each do |stop|
      stops << { :interest =>
                        { :name => stop.stop_name.titleize, 
                          :description => stop.description.titleize, 
                          :marker_icon => "marker_bus.png",
                          :picture => "nil",
                          :latitude => stop.stop_lat,
                          :longitude => stop.stop_lon,
                          :created_at => "2009-11-22T20:17:30Z",
                          :url => "",
                          :category_name => "METRO"
                        }
                     }
    end
    stops
  end

  def self.panoramio
	url = "http://www.panoramio.com/map/get_panoramas.php?order=popularity&set=public&from=0&to=100&minx=-95.3527021408081&miny=29.713923163642743&maxx=-95.3248929977417&maxy=29.72912917234306&size=medium"
	photos = Array.new
	JSON.parse(open(url).read)['photos'].each do |photo|
		description = "#{photo['owner_name']} - #{photo['upload_date']}"
		name = "Untitled"
		name = photo['photo_title'] if photo['photo_title'].size > 1
		photos << { :interest =>
				{ :name => name,
				:description => description,
				:marker_icon => "marker_photo.png",
				:picture => "nil",
				:latitude => photo['latitude'],
				:longitude => photo['longitude'],
				:created_at => "2009-11-22T20:17:30Z",
				:url => photo['photo_url'],
				# big pic? :url => photo['photo_file_url'],
				:category_name => "Photos"
			}
		}

	end
	photos

  end

end
