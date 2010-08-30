require 'rubygems'
require 'json'
require 'net/http'
require 'hpricot'

desc 'Update social media directory links'
task :update_social do 
  ENV["RAILS_ENV"] = 'production'
  RAILS_ENV.replace('production') if defined?(RAILS_ENV)

  load "#{RAILS_ROOT}/config/environment.rb"
    
  res = Net::HTTP.get_response(URI.parse('http://www.youarethepride.com/scripts/socialmedia_proc.php'))
  #res = File.read("socialmedia.json")

  array = JSON.parse(res.body)
  stuff = array[1]
  doc = Hpricot(stuff)

  (doc/"div.dir_linklist").reverse.each do |category|
  
    category_title = category.at("h3").inner_html
    social_category = SocialCategory.find_or_create_by_title(category_title)
      
    (category/ "ul/li").each do |link|

      a = link.at("a")
    
      social_link = SocialLink.find_or_create_by_title(a.attributes["title"])
      social_link.url = a.attributes["href"]
      social_link.network = link.attributes['class']
      social_link.icon = "http://uhcamp.us.to/images/#{link.attributes['class']}.png"
      social_link.social_category = social_category
      social_link.save
      
    end
  end
  puts "Updated social links"
end