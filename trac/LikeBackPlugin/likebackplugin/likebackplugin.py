# -*- coding: utf-8 -*-
"""
LikeBackPlugin:
a plugin for Trac
http://trac.edgewall.org
"""

from trac.core import *

from trac.ticket.api import ITicketChangeListener
import urllib
import urllib2
from urllib2 import HTTPError


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

        # First, retrieve the configuration options we will need.
        integration_secret = self.config.get( 'likeback-plugin', 'integration_secret' ) or self._die( 'no_conf', 'integration_secret' )
        likeback_url = self.config.get( 'likeback-plugin', 'likeback_url' ) or self._die( 'no_conf', 'likeback_url' )

        # Append the LikeBack Trac Signal script name to the URL
        likeback_url = likeback_url + 'trac_signal.php'

        # Fill up the list of changed ticket details
        values = {'author' : author.encode('utf-8'),
                  'comment' : comment.encode('utf-8'),
                  'ticketid' : ticket.id,
                  'summary' : ticket['summary'].encode('utf-8'),
                  'status' : ticket['status'].encode('utf-8'),
                  'resolution' : ticket['resolution'].encode('utf-8'),
                  'secret' : integration_secret.encode('utf-8')}

        # Send the signal
        try:
            data = urllib.urlencode( values )
            req = urllib2.Request( likeback_url, data )
            response = urllib2.urlopen( req )
            response = response.read()
            print response
        except HTTPError, e:
            if hasattr( e, 'code' ):
                error_code = e.code
            else:
                error_code = "404"

            if hasattr( e, 'reason' ):
                error_reason = e.reason
            else:
                error_reason = "Not Found!"

            self._die( 'net_error', "Got '%s: %s' error while loading '%s'" % ( error_code, error_reason, likeback_url ) )
        except ValueError:
            self._die( 'net_error', "The LikeBack URL is not valid: '%s'" % likeback_url )


    def ticket_created(self, ticket):
        """Called when a ticket is created.
        Don't do anything here, we don't care.
        """

    def ticket_deleted(self, ticket):
        """Called when a ticket is deleted.
        Don't do anything here, we don't care.
        """

    def _die(self, error, extrainfo ):
        """Outputs an error.
        """
        if error == 'no_conf':
            self.log.info( "LikeBack plugin configuration variable '%s' not found!" % extrainfo )
            raise TracError( "The LikeBack plugin is not correctly configured! The '%s' variable is missing. Please re-read the README file." % extrainfo )
        elif error == 'net_error':
            self.log.info( "Network error occurred in the LikeBack plugin. %s" % extrainfo )
            raise TracError( "The LikeBack plugin could not load its ticket updating system. %s" % extrainfo )
        else:
            self.log.info( "Unknown error occurred in the LikeBack plugin." )
            raise TracError( "Unknown Error! The LikeBack Plugin has encountered an unspecified error!" )
