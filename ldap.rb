require 'rubygems'
require 'net/ldap'

def ldap_search

 ldap = Net::LDAP.new
 ldap.host = "directory.uh.edu"
 ldap.port = "389"
 #ldap.auth "cn=Directory Manager", "password"
  treebase = "ou=Students,ou=Members,ou=People,o=University of Houston"
 filter2 = Net::LDAP::Filter.eq( "ou", "Student" )
 filter = Net::LDAP::Filter.eq( "cn", "*andre*" )  

 attrs = [ "ou" , "objectClass"]

 #ldap.search( :base => "o=University of Houston", :attributes => attrs, :filter =>
 #filter, :return_result => true ) do |entry|
 #  puts entry.inspect
 #end
puts  ldap.search( :base => treebase, :filter => filter ).size #do |entry|
#   puts "DN: #{entry.dn}"
#   entry.each do |attribute, values|
#     puts "   #{attribute}:"
#     values.each do |value|
#       puts "      --->#{value}"
#     end
#   end
# end

end 

ldap_search
