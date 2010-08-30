require 'rubygems'
require 'open-uri'
require 'youtube_g'
client = YouTubeG::Client.new


puts client.videos_by(:latitude => 29.72072515, :longitude => -95.34287452, :location_radius => "1km").inspect

