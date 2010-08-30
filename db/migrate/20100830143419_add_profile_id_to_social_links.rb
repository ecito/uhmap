class AddProfileIdToSocialLinks < ActiveRecord::Migration
  def self.up
    add_column :social_links, :profile_id, :integer
  end

  def self.down
    remove_column :social_links, :profile_id
  end
end
