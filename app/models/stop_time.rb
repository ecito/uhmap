class StopTime < ActiveRecord::Base
  belongs_to :stop
  belongs_to :trip
  has_one :route, :through => :trip
  
  def arrival_time
    Time.parse(self[:arrival_time])
  end
  
  def departure_time
    Time.parse(self[:departure_time])
  end
  
  #named_scope :next
end
