class TweetsController < ApplicationController
	
	def all

		render :text => open("http://search.twitter.com/search.json?q=from%3AUH_News+OR+from%3AUHIT+OR+from%3Athedailycougar+OR+from%3ACOOGCareers+OR+from%3AUHSGApres+OR+from%3AUHCougarSports+OR+from%3Ahoustonalumni").read
	end 


       def uh_news
		uh_news = Twitter::Search.new.from('UH_News').to_json
                render :json => uh_news if not uh_news.nil?
		render :json => "404".to_json if uh_news.nil?
        end
	
	def uh_traffic
		uh_traffic = Twitter::Search.new.from('UH_Traffic').to_json
		render :json => uh_trafic if not uh_traffic.nil?
		render :json => "404".to_json if uh_traffic.nil?
	end

	def campus
		coogs = Twitter::Search.new("#coogs").to_json
		uh = Twitter::Search.new("#UH").to_json # which hastag do we want?
		#geotagged = Twitter::Search.new("near:29.720856,-95.343904 within:1mi").to_json
		render :json => coogs if not coogs.nil?
		render :json => "404".to_json if coogs.nil?
	end
end
