class AddPictureToInterests < ActiveRecord::Migration
  def self.up
    add_column :interests, :picture, :string
  end

  def self.down
    remove_column :interests, :picture
  end
end

