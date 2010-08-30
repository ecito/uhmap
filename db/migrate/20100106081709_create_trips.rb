class CreateTrips < ActiveRecord::Migration
  def self.up
    create_table :trips do |t|
      t.integer :route_id
      t.integer :service_id
      t.string :trip_headsign
      t.integer :direction_id
      t.integer :block_id
      t.integer :shape_id

    end
  end

  def self.down
    drop_table :trips
  end
end
