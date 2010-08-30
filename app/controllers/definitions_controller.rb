class DefinitionsController < ApplicationController
	def define
		word = params[:word]
		url = "http://dictionary.reference.com/browse/#{word}"
		html = open(url).read
		matches = /class="dnindex">\d.<\/td>\s?<td>([^<]*)<\/td>/.match(html)
		
		unless matches.nil?
			render :text => matches[1].capitalize
		else
			
			#url = "http://www.thefreedictionary.com/#{word}"
			#matches = /<div class="ds-list"><b>\d.\s?<\/b>\s?([^<]*)<span/.match(html)
			url = "http://www.google.com/search?q=define:#{word}"
			html = open(url).read
			matches = /<ul type="disc" class=std><li>([^<]*)<li>/.match(html)
						
			unless matches.nil?
				render :text => CGI.unescapeHTML(matches[1].capitalize)
			else 
				render :text => "Sorry, no hint found."
			end
			
		end
	end

end
