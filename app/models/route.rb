class Route < ActiveRecord::Base
  has_many :trips
  
  def unique_trips
    self.trips.find(:all, :group => "trip_headsign")
  end
  
end
