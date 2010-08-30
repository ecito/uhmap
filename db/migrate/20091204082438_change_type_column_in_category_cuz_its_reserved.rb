class ChangeTypeColumnInCategoryCuzItsReserved < ActiveRecord::Migration
  def self.up
	rename_column :categories, :type, :source
  end

  def self.down
	rename_column :categories, :source, :type
  end
end
