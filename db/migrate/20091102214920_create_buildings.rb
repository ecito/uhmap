class CreateBuildings < ActiveRecord::Migration
  def self.up
    create_table :buildings do |t|
      t.string :name
      t.integer :number
      t.string :address
      t.string :description
      t.float :latitude
      t.float :longitude
      t.string :code

      t.timestamps
    end
  end

  def self.down
    drop_table :buildings
  end
end
