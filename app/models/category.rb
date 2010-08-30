class Category < ActiveRecord::Base
	validates_uniqueness_of :name
	has_many :interests
end
