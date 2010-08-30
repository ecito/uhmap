class PeopleController < ApplicationController

	def search
		entries = Person.ldap_search params["name"], params["affiliation"] unless params["name"].nil?
		render :json => entries
	end
end
