# This file should contain all the record creation needed to seed the database with its default values.
# The data can then be loaded with the rake db:seed (or created alongside the db with db:setup).
#
# Examples:
#   
#   cities = City.create([{ :name => 'Chicago' }, { :name => 'Copenhagen' }])
#   Major.create(:name => 'Daley', :city => cities.first)

Category.create(:name => 'Parking', :description => 'Live parking status', :default_marker => 'parking.png', :source => 'dynamic')
Category.create(:name => 'Restaurants', :description => 'Restaurants on campus', :default_marker => 'restaurant.png', :source => 'static')
Category.create(:name => 'Events', :description => 'Events on campus', :default_marker => 'event.png', :source => 'static')
Category.create(:name => 'Shuttles', :description => 'Shuttle busses', :default_marker => 'shuttle.png', :source => 'static')
Category.create(:name => 'METRO', :description => 'METRO City Buses', :default_marker => 'metro.png', :source => 'static')
Category.create(:name => 'Videos', :description => 'Youtube videos on campus', :default_marker => 'youtube.png', :source => 'dynamic')
Category.create(:name => 'Pictures', :description => 'Panoramio pictures on campus', :default_marker => 'panoramio.png', :source => 'dynamic')
Category.create(:name => 'Wikipedia', :description => 'Wikipedia articles on campus', :default_marker => 'wikipedia.png', :source => 'dynamic')
