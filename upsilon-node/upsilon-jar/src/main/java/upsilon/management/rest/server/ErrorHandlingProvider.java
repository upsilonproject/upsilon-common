package upsilon.management.rest.server;

import javax.ws.rs.core.Response;
import javax.ws.rs.ext.ExceptionMapper;
import javax.ws.rs.ext.Provider;

import com.sun.jersey.api.NotFoundException;

@Provider 
public class ErrorHandlingProvider implements ExceptionMapper<NotFoundException> {
    @Override
	public Response toResponse(NotFoundException exception) {  
        return Response.status(Response.Status.NOT_FOUND).entity("HTTP 404 Not Found").build();
    }  
}
