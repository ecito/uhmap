class Trip < ActiveRecord::Base
  has_many :stop_times
  belongs_to :route
end
