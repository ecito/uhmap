class SocialCategory < ActiveRecord::Base
  has_many :social_links
  validates_uniqueness_of :title
end
