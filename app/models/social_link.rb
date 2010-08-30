class SocialLink < ActiveRecord::Base
  belongs_to :social_category
  validates_uniqueness_of :title
end
