class Stop < ActiveRecord::Base
  has_many :stop_times
  has_many :trips, :through => :stop_times
  
  def next_departure
    self.stop_times.detect {|time| time.departure_time > Time.now }
  end

  def unique_trips
    self.trips.find(:all, :group => "trip_headsign")
  end
  
  def unique_route_names
    trips = Array.new
    self.unique_trips.each {|trip|  trips << trip.route.route_long_name}
    trips.uniq
  end
  
  def unique_routes
    routes = Array.new
    self.unique_route_names.each {|route| routes << Route.find_by_route_long_name(route)}
    routes
  end
  
  def description
    self.unique_route_names.join("\n\n")
  end
  
end

