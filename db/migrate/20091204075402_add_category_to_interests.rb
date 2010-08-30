class AddCategoryToInterests < ActiveRecord::Migration
  def self.up
	remove_column :interests, :category
	add_column :interests, :category_id, :integer
  end

  def self.down
	remove_colum :interests, :category_id
	add_column :interests, :category, :string
  end
end
