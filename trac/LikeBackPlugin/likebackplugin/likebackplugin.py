"""
LikeBackPlugin:
a plugin for Trac
http://trac.edgewall.org
"""

from trac.core import *

from trac.ticket.api import ITicketChangeListener
import urllib
import urllib2

# alter these yourself:
likeback_url = "http://kmess.org/likeback/backend/trac_signal.php"
integration_secret = ""

class LikeBackPlugin(Component):

    implements(ITicketChangeListener)

    ### methods for ITicketChangeListener

    """Extension point interface for components that require notification
    when tickets are created, modified, or deleted."""

    def ticket_changed(self, ticket, comment, author, old_values):
        """Called when a ticket is modified.
        
        `old_values` is a dictionary containing the previous values of the
        fields that have changed.
        Notify LikeBack, it will handle the rest.
        """
        values = {'author' : author,
                  'comment' : comment,
                  'ticketid' : ticket.id,
                  'summary' : ticket['summary'],
                  'status' : ticket['status'],
                  'resolution' : ticket['resolution'],
                  'secret' : integration_secret}
        data = urllib.urlencode( values )
        req = urllib2.Request( likeback_url, data )
        response = urllib2.urlopen( req )
        response = response.read()
        print response

    def ticket_created(self, ticket):
        """Called when a ticket is created.
        Don't do anything here, we don't care.
        """

    def ticket_deleted(self, ticket):
        """Called when a ticket is deleted.
        Don't do anything here, we don't care.
        """
