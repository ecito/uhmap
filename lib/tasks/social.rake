# require 'rubygems'
# require 'json'
# require 'net/http'
# require 'hpricot'

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
      social_link.icon = "http://uhcamp.us.to/images/social/#{link.attributes['class']}.png"
      social_link.social_category = social_category
      
      if social_link.network == "facebook"
        #puts "trying to get profile_id at #{social_link.url}"         
        
        social_link.profile_id = split_path(social_link.url)
        if social_link.profile_id < 1
           social_link.profile_id = scrape(social_link.url)
        end
        
        #puts "FOUND #{social_link.profile_id}"
        unless social_link.profile_id.nil? or social_link.profile_id < 1
          social_link.icon = "http://graph.facebook.com/#{social_link.profile_id}/picture" 
          social_link.url = "htt://touch.facebook.com/#/profile.php?id=#{social_link.profile_id}"
        end
      elsif social_link.network == "twitter"
        social_link.icon = "http://img.tweetimag.es/i/#{split_path_twitter(social_link.url)}_n.png"
      end

      social_link.save
      
    end
  end
  puts "Updated social links"
end

def split_path_twitter url
  Pathname.new(URI.split(url)[5]).basename.to_s
end

def split_path url
  begin
   page_id = Pathname.new(URI.split(url)[5]).basename.to_s.to_i
    if page_id < 1
      puts "resorting to GID"
      page_id = CGI::parse(URI.split(url)[7])["gid"][0].to_i
    end
  rescue Exception => e
  end
  page_id
end

def scrape url
  begin  
    doc =  Hpricot(open(url, "User-Agent" => "Mozilla 4.0").read)

    puts "*********RESORTING TO SCRAPE 1"
  
    begin
      page_id = CGI::parse(doc.at("link[@type=application/rss+xml]").attributes["href"])["id"][0].to_i
    rescue Exception => e
      begin
        puts "*********RESORTING TO SCRAPE 2"
        page_id = CGI::parse(doc.at("a.uiButton").attributes["href"])["fid"][0].to_i
      rescue Exception => e
      end
    end
  rescue Exception => e
  end
  page_id
end