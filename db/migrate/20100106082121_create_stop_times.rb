class CreateStopTimes < ActiveRecord::Migration
  def self.up
    create_table :stop_times do |t|
      t.integer :trip_id
      t.string :arrival_time
      t.string :departure_time
      t.integer :stop_id
      t.string :stop_sequence
      t.string :stop_headsign
      t.string :pickup_type
      t.string :drop_off_type
      t.string :shape_dist_traveled

    end
  end

  def self.down
    drop_table :stop_times
  end
end
