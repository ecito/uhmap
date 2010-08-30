class NewsController < ApplicationController
	def articles
		feed = RSS::Parser.parse("http://www.thedailycougar.com/se/the-daily-cougar-rss-1.778701")
		render :json => feed.items.to_json
	end

	def get_html_from
		page = Hpricot( open( 'http://www.thedailycougar.com/football-cougars-stun-ap-no-5-oklahoma-state-1.1875087' ) )
	#	page = Hpricot(open(params[:link]))
		render :text => page.search( "//div[@class='article']" ).inner_html
	end

end
