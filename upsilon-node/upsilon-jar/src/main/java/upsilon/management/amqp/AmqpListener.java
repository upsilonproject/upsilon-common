package upsilon.management.amqp;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Daemon;
import upsilon.Main;

import com.rabbitmq.client.Channel;
import com.rabbitmq.client.Connection;
import com.rabbitmq.client.ConnectionFactory;
import com.rabbitmq.client.QueueingConsumer;

public class AmqpListener extends Daemon implements Runnable {
	private static final transient Logger LOG = LoggerFactory.getLogger(AmqpListener.class);

	private QueueingConsumer consumer;
	private boolean run = true;

	private Channel channel;

	public AmqpListener() throws Exception {
		String hostname = "localhost";

		AmqpListener.LOG.info("Starting the AMQP listener, connecting to host: " + hostname);
		this.start(hostname);
	}

	@Override
	public void run() {
		try {
			this.channel.basicConsume("upsilon", this.consumer);

			while (this.run) {
				QueueingConsumer.Delivery delivery = this.consumer.nextDelivery();

				String message = new String(delivery.getBody());
				AmqpListener.LOG.debug("recv: " + message);

				switch (message) {
				case "query-version":
					this.channel.basicPublish("upsilon", "upsilon", null, ("version: " + Main.getVersion()).getBytes());
					this.channel.basicAck(delivery.getEnvelope().getDeliveryTag(), false);
					break;
				default:
					LOG.warn("Unsupported message: " + message);
				}
			}
		} catch (Exception e) {
			this.stop();
		}
	}

	public void start(String hostname) throws Exception {
		ConnectionFactory factory = new ConnectionFactory();
		factory.setHost(hostname);

		try {
			Connection connection = factory.newConnection();
			this.channel = connection.createChannel();
			this.channel.exchangeDeclare("ex_upsilon", "fanout");
			this.channel.queueDeclare("upsilon", false, false, false, null);

			this.consumer = new QueueingConsumer(this.channel);
		} catch (Exception e) {
			AmqpListener.LOG.error("Could not complete initial AMQP binding: " + e.getMessage(), e);
			return;
		}

		AmqpListener.LOG.info("AMQP connection looks good.");

		// channel.basicConsume("upsilon", true, this.consumer);

		this.run = true;
	}

	@Override
	public void stop() {
		this.run = false;
	}
}
